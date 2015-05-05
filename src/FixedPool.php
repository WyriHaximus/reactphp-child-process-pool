<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use Evenement\EventEmitter;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class FixedPool extends EventEmitter
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
     * @var int
     */
    protected $size;

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

    protected $timer;

    /**
     * @param Process $process
     * @param LoopInterface $loop
     * @param int $size
     */
    public function __construct(Process $process, LoopInterface $loop, $size = 25)
    {
        $this->sourceProcess = $process;
        $this->loop = $loop;
        $this->size = $size;

        $this->readyPool = new \SplQueue();
        $this->pool = new \SplObjectStorage();

        $this->callQueue = new \SplQueue();

        for ($i = 0; $i < $size; $i++) {
            $this->spawnProcess();
        }
    }

    protected function spawnProcess()
    {
        $process = clone $this->sourceProcess;
        $messenger = new Messenger($process);
        $messenger->start($this->loop)->then(function (Messenger $messenger) {
            $this->pool->attach($messenger);
            $this->readyPool->enqueue($messenger);
        }, function ($error) {
            var_export($error);die();
            $this->emit('error', [$error, $this]);
        });
    }

    /**
     * @param Call $message
     * @return PromiseInterface
     */
    public function rpc(Call $message)
    {
        if ($this->callQueue->count() == 0 && $this->readyPool->count() > 0) {
            return $this->sendRpc($message);
        }

        return $this->queueRpc($message);
    }

    /**
     * @param Call $message
     * @return PromiseInterface
     */
    protected function queueRpc(Call $message)
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
     * @param Call $message
     * @return PromiseInterface
     */
    protected function sendRpc(Call $message)
    {
        $messenger = $this->readyPool->dequeue();
        return $messenger->rpc($message)->then(function ($data) use ($messenger) {
            $this->readyPool->enqueue($messenger);
            $this->checkQueue();
            return \React\Promise\resolve($data);
        }, function ($error) use ($messenger) {
            var_export($error);die();
            $this->readyPool->enqueue($messenger);
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

    public function terminate($signal = null)
    {
        foreach ($this->pool as $messenger) {
            $messenger->terminate($signal);
        }
    }
}
