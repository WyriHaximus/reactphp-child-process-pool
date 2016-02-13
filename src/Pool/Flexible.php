<?php

namespace WyriHaximus\React\ChildProcess\Pool\Pool;

use Evenement\EventEmitterTrait;
use React\ChildProcess\Process as ChildProcess;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Pool\Info;
use WyriHaximus\React\ChildProcess\Pool\Launcher\ClassName;
use WyriHaximus\React\ChildProcess\Pool\Launcher\Process;
use WyriHaximus\React\ChildProcess\Pool\LoopAwareTrait;
use WyriHaximus\React\ChildProcess\Pool\Manager\Flexible as FlexibleManager;
use WyriHaximus\React\ChildProcess\Pool\ManagerInterface;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollection\Single;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;
use WyriHaximus\React\ChildProcess\Pool\Queue\Memory;
use WyriHaximus\React\ChildProcess\Pool\QueueInterface;
use WyriHaximus\React\ChildProcess\Pool\WorkerInterface;

class Flexible implements PoolInterface
{
    use EventEmitterTrait;
    use LoopAwareTrait;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Deferred[]
     */
    protected $deferreds = [];

    /**
     * @var array
     */
    protected $options = [
        Options::MIN_SIZE => 0,
        Options::MAX_SIZE => 5,
    ];

    public static function create(ChildProcess $process, LoopInterface $loop, array $options = [])
    {
        return \React\Promise\resolve(new self(new Single(new Process($process)), $loop, $options));
    }

    public static function createFromClass($class, LoopInterface $loop, array $options = [])
    {
        return \React\Promise\resolve(new self(new Single(new ClassName($class)), $loop, $options));
    }

    public function __construct(ProcessCollectionInterface $processCollection, LoopInterface $loop, array $options = [])
    {
        $this->loop = $loop;
        $this->options = array_merge($this->options, $options);
        $this->queue   = \WyriHaximus\React\ChildProcess\Pool\getQueue(
            $this->options,
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            $loop
        );
        $this->manager = \WyriHaximus\React\ChildProcess\Pool\getManager(
            $this->options,
            $processCollection,
            'WyriHaximus\React\ChildProcess\Pool\Manager\Flexible',
            $loop
        );
        $this->queue = new Memory();
        $this->manager = new FlexibleManager($processCollection, $loop, $this->options);
        $this->manager->on('ready', function (WorkerInterface $worker) {
            if ($this->queue->count() === 0) {
                $worker->terminate();
                return;
            }
            $message = $this->queue->dequeue();
            $hash = spl_object_hash($message);
            $this->deferreds[$hash]->resolve($worker->rpc($message));
        });
    }

    public function rpc(Rpc $message)
    {
        $hash = spl_object_hash($message);
        $this->deferreds[$hash] = new Deferred();
        $this->queue->enqueue($message);
        $this->manager->ping();
        return $this->deferreds[$hash]->promise();
    }

    public function message(Message $message)
    {
        // TODO: Implement message() method.
    }

    public function terminate(Message $message = null, $timeout = 5, $signal = null)
    {
        if ($message !== null) {
            $this->message($message);
        }

        return \WyriHaximus\React\timedPromise($this->loop, $timeout)->then(function () {
            return $this->manager->terminate();
        });
    }

    public function info()
    {
        $workers = $this->manager->info();
        return [
            Info::BUSY  => $workers[Info::BUSY],
            Info::CALLS => $this->queue->count(),
            Info::IDLE  => $workers[Info::IDLE],
            Info::SIZE  => $workers[Info::TOTAL],
        ];
    }
}
