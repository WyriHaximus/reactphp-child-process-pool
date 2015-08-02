<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use WyriHaximus\React\ChildProcess\Pool\FixedPool;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

class FixedPoolTest extends AbstractPoolTest
{
    /**
     * @return PoolInterface
     */
    public function getPool()
    {
        return new FixedPool($this->process, $this->loop);
    }
}
