<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use WyriHaximus\React\ChildProcess\Pool\CpuCoreCountFixedPool;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

class CpuCoreCountFixedPoolTest extends AbstractPoolTest
{
    /**
     * @return PoolInterface
     */
    public function getPool()
    {
        return new CpuCoreCountFixedPool($this->process, $this->loop);
    }
}
