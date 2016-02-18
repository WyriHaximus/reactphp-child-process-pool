<?php

namespace WyriHaximus\React\ChildProcess\Pool\Pool;

use Evenement\EventEmitterTrait;
use React\ChildProcess\Process as ChildProcess;
use React\EventLoop\LoopInterface;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Pool\Launcher\ClassName;
use WyriHaximus\React\ChildProcess\Pool\Launcher\Process;
use WyriHaximus\React\ChildProcess\Pool\PoolFactoryInterface;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollection\Single;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;

class Dummy implements PoolInterface, PoolFactoryInterface
{
    use EventEmitterTrait;

    /**
     * @param ChildProcess $process
     * @param LoopInterface $loop
     * @param array $options
     * @return PromiseInterface
     */
    public static function create(ChildProcess $process, LoopInterface $loop, array $options = [])
    {
        return \React\Promise\resolve(new self(new Single(new Process($process)), $loop, $options));
    }

    /**
     * @param string $class
     * @param LoopInterface $loop
     * @param array $options
     * @return PromiseInterface
     */
    public static function createFromClass($class, LoopInterface $loop, array $options = [])
    {
        return \React\Promise\resolve(new self(new Single(new ClassName($class)), $loop, $options));
    }

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

    public function terminate(Message $message = null, $timeout = 5, $signal = null)
    {
    }

    public function info()
    {
        return [];
    }
}
