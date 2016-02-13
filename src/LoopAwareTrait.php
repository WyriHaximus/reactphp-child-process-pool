<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use React\EventLoop\LoopInterface;

trait LoopAwareTrait
{
    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
