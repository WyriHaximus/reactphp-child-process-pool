<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use Evenement\EventEmitter;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\FulfilledPromise;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

class DummyPool extends EventEmitter implements PoolInterface
{
    public function __construct(Process $process, LoopInterface $loop, array $options = [])
    {

    }

    public function rpc(Rpc $message)
    {
        return new FulfilledPromise();
    }

    public function message(Message $message)
    {
    }

    public function terminate($message, $timeout = 5, $signal = null)
    {
    }

    public function info()
    {
        return [];
    }
}
