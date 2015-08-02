<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use WyriHaximus\React\ChildProcess\Pool\FlexiblePool;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

class FlexiblePoolTest extends AbstractPoolTest
{
    /**
     * @return PoolInterface
     */
    public function getPool()
    {
        return new FlexiblePool($this->process, $this->loop);
    }
}
