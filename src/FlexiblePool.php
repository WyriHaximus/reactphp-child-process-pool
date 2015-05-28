<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use Evenement\EventEmitter;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class FlexiblePool extends EventEmitter implements PoolInterface
{
    const INTERVAL = 0.01;

    /**
     * @var Process
     */
    protected $sourceProcess;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var array
     */
    protected $options = [
        'min_size' => 0,
        'max_size' => 5,
    ];

    /**
     * @var \SplQueue
     */
    protected $readyPool;

    /**
     * @var \SplObjectStorage
     */
    protected $pool;

    /**
     * @var \SplQueue
     */
    protected $callQueue;

    /**
     * @var null|TimerInterface
     */
    protected $timer;

    protected $startingProcesses = 0;

    /**
     * @param Process $process
     * @param LoopInterface $loop
     * @param array $options
     */
    public function __construct(Process $process, LoopInterface $loop, array $options = [])
    {
        $this->sourceProcess = $process;
        $this->loop = $loop;
        $this->options = array_merge($this->options, $options);

        $this->readyPool = new \SplQueue();
        $this->pool = new \SplObjectStorage();

        $this->callQueue = new \SplQueue();

        for ($i = 0; $i < $this->options['min_size']; $i++) {
            $this->spawnProcess();
        }
    }

    protected function spawnProcess()
    {
        $this->startingProcesses++;
        $processOptions = isset($this->options['processOptions']) ? $this->options['processOptions'] : [];
        $process = clone $this->sourceProcess;
        Factory::parent($process, $this->loop, $processOptions)->then(function (Messenger $messenger) {
            $this->startingProcesses--;
            $this->pool->attach($messenger);
            $this->readyPool->enqueue($messenger);
        }, function ($error) {
            $this->emit('error', [$error, $this]);
        });
    }

    protected function shouldShutDownMessenger(Messenger $messenger)
    {
        if ($this->callQueue->count() == 0 && $this->pool->count() > $this->options['min_size']) {
            $this->pool->detach($messenger);
            $messenger->terminate();
            return;
        }

        $this->readyPool->enqueue($messenger);
    }

    /**
     * @param Rpc $message
     * @return PromiseInterface
     */
    public function rpc(Rpc $message)
    {
        if ($this->callQueue->count() == 0 && $this->readyPool->count() > 0) {
            return $this->sendRpc($message);
        }

        if ($this->callQueue->count() > 0 && $this->readyPool->count() == 0 && ($this->startingProcesses + $this->pool->count()) < $this->options['max_size']) {
            $this->spawnProcess();
        }

        return $this->queueRpc($message);
    }

    /**
     * @param Rpc $message
     * @return PromiseInterface
     */
    protected function queueRpc(Rpc $message)
    {
        $deferred = new Deferred();
        $this->callQueue->enqueue($deferred);

        if ($this->timer === null || !$this->loop->isTimerActive($this->timer)) {
            $this->timer = $this->loop->addTimer(static::INTERVAL, function () {
                $this->checkQueue();
            });
        }

        return $deferred->promise()->then(function () use ($message) {
            return $this->sendRpc($message);
        });
    }

    /**
     * @param Rpc $message
     * @return PromiseInterface
     */
    protected function sendRpc(Rpc $message)
    {
        $messenger = $this->readyPool->dequeue();
        return $messenger->rpc($message)->then(function ($data) use ($messenger) {
            $this->shouldShutDownMessenger($messenger);
            $this->checkQueue();
            return \React\Promise\resolve($data);
        }, function ($error) use ($messenger) {
            $this->shouldShutDownMessenger($messenger);
            $this->checkQueue();
            return \React\Promise\reject($error);
        });
    }

    protected function checkQueue()
    {
        if ($this->callQueue->count() > 0 && $this->readyPool->count() > 0) {
            $this->callQueue->dequeue()->resolve();
        }

        if ($this->callQueue->count() > 0) {
            $this->timer = $this->loop->addTimer(static::INTERVAL, function () {
                $this->checkQueue();
            });
        }
    }

    public function message(Message $message)
    {
        foreach ($this->pool as $messenger) {
            $messenger->message($message);
        }
    }

    public function terminate($message, $timeout = 5, $signal = null)
    {
        $this->message($message);

        while ($this->readyPool->count() > 0) {
            $this->readyPool->dequeue();
        }

        $this->pool->rewind();
        while ($this->pool->count() > 0) {
            $messenger = $this->pool->current();
            $this->pool->detach($messenger);
            $messenger->terminate($signal);
        }
    }

    public function info()
    {
        return [
            'size'          => $this->pool->count(),
            'queued_calls'  => $this->callQueue->count(),
            'idle_workers'  => $this->readyPool->count(),
        ];
    }
}
