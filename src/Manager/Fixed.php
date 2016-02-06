<?php

namespace WyriHaximus\React\ChildProcess\Pool\Manager;

use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Pool\ManagerInterface;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;
use WyriHaximus\React\ChildProcess\Pool\Worker;
use WyriHaximus\React\ChildProcess\Pool\WorkerInterface;

class Fixed implements ManagerInterface
{
    use EventEmitterTrait;

    /**
     * @var WorkerInterface[]
     */
    protected $workers = [];

    public function __construct(ProcessCollectionInterface $processCollection, LoopInterface $loop, array $options = [])
    {
        $workerDone = function (WorkerInterface $worker) {
            $this->workerAvailable($worker);
        };
        $processCollection->rewind();
        for ($i = 0; $i < $options['size']; $i++) {
            $current = $processCollection->current();
            $promise = $current($loop, $options);
            $promise->then(function (Messenger $messenger) use ($workerDone) {
                $worker = new Worker($messenger);
                $this->workers[] = $worker;
                $worker->on('done', $workerDone);
                $this->workerAvailable($worker);
            });
            if (!$processCollection->next()) {
                $processCollection->rewind();
            }
        }
    }

    protected function workerAvailable(WorkerInterface $worker)
    {
        $this->emit('ready', [$worker]);
    }

    public function ping()
    {
        foreach ($this->workers as $worker) {
            if (!$worker->isBusy()) {
                $this->workerAvailable($worker);
            }
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
            'total' => $count,
            'busy' => $busy,
            'idle' => $count - $busy,
        ];
    }
}