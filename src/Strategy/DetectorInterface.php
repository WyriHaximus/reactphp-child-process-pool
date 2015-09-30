<?php

namespace WyriHaximus\React\ChildProcess\Pool\Strategy;

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Pool\StrategyInterface;

interface DetectorInterface extends StrategyInterface
{
    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop);
}
