<?php

namespace WyriHaximus\React\ChildProcess\Pool\Pool;

use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use React\Promise\FulfilledPromise;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;

class DummyPool implements PoolInterface
{
    use EventEmitterTrait;

    public function __construct(ProcessCollectionInterface $processCollection, LoopInterface $loop, array $options = [])
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
