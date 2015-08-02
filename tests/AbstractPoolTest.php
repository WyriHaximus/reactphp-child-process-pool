<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

abstract class AbstractPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @return PoolInterface
     */
    abstract public function getPool();

    protected function getLoop()
    {
        return \Phake::mock(LoopInterface::class);
    }

    protected function getProcess()
    {
        return \Phake::mock(Process::class);
    }

    public function setUp()
    {
        parent::setUp();
        $this->loop = $this->getLoop();
        $this->process = $this->getProcess();
        $this->pool = $this->getPool();
    }

    public function tearDown()
    {
        unset($this->loop, $this->process, $this->pool);
        parent::tearDown();
    }

    public function testInterface()
    {
        $this->assertInstanceOf(PoolInterface::class, $this->pool);
    }
}
