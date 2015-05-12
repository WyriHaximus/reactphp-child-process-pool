<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use Evenement\EventEmitterInterface;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;

interface PoolInterface extends EventEmitterInterface
{
    /**
     * @param Process $process
     * @param LoopInterface $loop
     * @param array $options
     */
    public function __construct(Process $process, LoopInterface $loop, array $options = []);

    /**
     * @param Call $message
     * @return PromiseInterface
     */
    public function rpc(Call $message);

    /**
     * @param string $message
     * @param int $timeout
     * @param null $signal
     * @return mixed
     */
    public function terminate($message, $timeout = 5, $signal = null);

    /**
     * @return array
     */
    public function info();
}
