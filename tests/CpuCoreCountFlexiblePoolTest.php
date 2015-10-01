<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use WyriHaximus\React\ChildProcess\Pool\CpuCoreCountFlexiblePool;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

class CpuCoreCountFlexiblePoolTest extends AbstractPoolTest
{
    /**
     * @return PoolInterface
     */
    public function getPool()
    {
        return new CpuCoreCountFlexiblePool($this->process, $this->loop);
    }
}
