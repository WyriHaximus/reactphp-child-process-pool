<?php

namespace WyriHaximus\React\ChildProcess\Pool\Manager;

use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Pool\Info;
use WyriHaximus\React\ChildProcess\Pool\ManagerInterface;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;
use WyriHaximus\React\ChildProcess\Pool\Worker;
use WyriHaximus\React\ChildProcess\Pool\WorkerInterface;

class Fixed implements ManagerInterface
{
    use EventEmitterTrait;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var ProcessCollectionInterface
     */
    protected $processesCollection;

    /**
     * @var WorkerInterface[]
     */
    protected $workers = [];

    /**
     * @var array
     */
    protected $options;

    /**
     * @var int
     */
    protected $workerCount = 0;

    /**
     * @var int
     */
    protected $terminatingCount = 0;

    public function __construct(ProcessCollectionInterface $processCollection, LoopInterface $loop, array $options = [])
    {
        $this->options = $options;
        $this->loop = $loop;
        $this->processesCollection = $processCollection;
        $processCollection->rewind();
        $this->spawnWorkers($this->options[Options::SIZE]);
    }

    protected function spawnWorkers($count)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->spawn($this->processesCollection, $this->options);
        }
    }

    protected function spawn($processCollection, $options)
    {
        $this->workerCount++;
        $workerDone = function (WorkerInterface $worker) {
            if ($this->workerCount > $this->options[Options::SIZE]) {
                $worker->terminate();
                $this->workerCount--;
                return;
            }

            $this->workerAvailable($worker);
        };
        $current = $processCollection->current();
        $promise = $current($this->loop, $options);
        $promise->then(function (Messenger $messenger) use ($workerDone) {
            $worker = new Worker($messenger);
            $this->workers[spl_object_hash($worker)] = $worker;
            $worker->on('done', $workerDone);
            $worker->on('terminating', function (WorkerInterface $worker) {
                unset($this->workers[spl_object_hash($worker)]);
                $this->workerCount--;
                $this->terminatingCount++;
            });
            $worker->on('terminated', function (WorkerInterface $worker) {
                $this->terminatingCount--;
            });
            $this->workerAvailable($worker);
        }, function () {
            $this->workerCount--;
        });

        $processCollection->next();
        if (!$processCollection->valid()) {
            $processCollection->rewind();
        }
    }

    protected function workerAvailable(WorkerInterface $worker)
    {
        $this->emit('ready', [$worker]);
    }

    public function ping()
    {
        if ($this->workerCount < $this->options[Options::SIZE]) {
            $this->spawnWorkers($this->options[Options::SIZE] - $this->workerCount);
        }

        foreach ($this->workers as $worker) {
            if (!$worker->isBusy()) {
                $this->workerAvailable($worker);
            }
        }
    }

    public function message(Message $message)
    {
        foreach ($this->workers as $worker) {
            $worker->message($message);
        }
    }

    public function terminate()
    {
        $promises = [];

        foreach ($this->workers as $worker) {
            $promises[] = $worker->terminate();
        }

        return \React\Promise\all($promises);
    }

    public function info()
    {
        $count = count($this->workers);

        $busy = 0;
        foreach ($this->workers as $worker) {
            if ($worker->isBusy()) {
                $busy++;
            }
        }

        return [
            Info::TOTAL       => $this->workerCount + $this->terminatingCount,
            Info::STARTING    => $this->workerCount - $count,
            Info::RUNNING     => $count + $this->terminatingCount,
            Info::TERMINATING => $this->terminatingCount,
            Info::BUSY        => $busy,
            Info::IDLE        => $count - $busy,
        ];
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
        $this->ping();
    }
}
