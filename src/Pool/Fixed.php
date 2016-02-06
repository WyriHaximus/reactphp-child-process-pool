<?php

namespace WyriHaximus\React\ChildProcess\Pool\Pool;

use Evenement\EventEmitterTrait;
use React\ChildProcess\Process as ChildProcess;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Pool\Launcher\ClassName;
use WyriHaximus\React\ChildProcess\Pool\Launcher\Process;
use WyriHaximus\React\ChildProcess\Pool\ManagerInterface;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollection\Single;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;
use WyriHaximus\React\ChildProcess\Pool\QueueInterface;
use WyriHaximus\React\ChildProcess\Pool\WorkerInterface;

class Fixed implements PoolInterface
{
    use EventEmitterTrait;

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
        Options::SIZE => 25,
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
        $this->loop    = $loop;
        $this->options = array_merge($this->options, $options);
        $this->queue   = $this->getQueue($options);
        $this->manager = $this->getManager($options, $processCollection);
        $this->manager->on('ready', function (WorkerInterface $worker) {
            if ($this->queue->count() === 0) {
                return;
            }
            $message = $this->queue->dequeue();
            $hash = spl_object_hash($message);
            $this->deferreds[$hash]->resolve($worker->rpc($message));
        });
    }

    protected function getQueue(array $options)
    {
        $queue = \WyriHaximus\React\ChildProcess\Pool\getClassNameFromOptionOrDefault(
            $options,
            Options::QUEUE,
            'WyriHaximus\React\ChildProcess\Pool\QueueInterface',
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory'
        );
        return new $queue();
    }

    protected function getManager(array $options, $processCollection)
    {
        $manager = \WyriHaximus\React\ChildProcess\Pool\getClassNameFromOptionOrDefault(
            $options,
            Options::QUEUE,
            'WyriHaximus\React\ChildProcess\Pool\ManagerInterface',
            'WyriHaximus\React\ChildProcess\Pool\Manager\Fixed'
        );
        return new $manager($processCollection, $this->loop, $this->options);
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
            'size'          => $workers['total'],
            'queued_calls'  => $this->queue->count(),
            'idle_workers'  => $workers['idle'],
            'busy_workers'  => $workers['busy'],
        ];
    }
}
