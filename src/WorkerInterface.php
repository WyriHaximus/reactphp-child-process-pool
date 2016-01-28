<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use Evenement\EventEmitterInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

interface WorkerInterface extends EventEmitterInterface
{
    /**
     * @param Messenger $messenger
     */
    public function __construct(Messenger $messenger);

    /**
     * @param Rpc $rpc
     * @return PromiseInterface
     */
    public function rpc(Rpc $rpc);

    /**
     * @return bool
     */
    public function isBusy();

    /**
     * @return PromiseInterface
     */
    public function terminate();
}
