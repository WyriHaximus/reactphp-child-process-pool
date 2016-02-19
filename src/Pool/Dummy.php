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

    /**
     * @param ProcessCollectionInterface $processCollection
     * @param LoopInterface $loop
     * @param array $options
     */
    public function __construct(ProcessCollectionInterface $processCollection, LoopInterface $loop, array $options = [])
    {
    }

    /**
     * @param Rpc $message
     * @return PromiseInterface
     */
    public function rpc(Rpc $message)
    {
        return new FulfilledPromise();
    }

    /**
     * @param Message $message
     */
    public function message(Message $message)
    {
    }

    /**
     * @param Message|null $message
     * @param int $timeout
     * @param null $signal
     */
    public function terminate(Message $message = null, $timeout = 5, $signal = null)
    {
    }

    /**
     * @return array
     */
    public function info()
    {
        return [];
    }
}
