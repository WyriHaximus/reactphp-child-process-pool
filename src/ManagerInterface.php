<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

interface ManagerInterface extends EventEmitterInterface
{
    /**
     * @param ProcessCollectionInterface $processCollection
     * @param array $options
     */
    public function __construct(
        ProcessCollectionInterface $processCollection,
        LoopInterface $loop,
        array $options = []
    );

    /**
     * @return PromiseInterface
     */
    public function terminate();

    public function ping();
}
