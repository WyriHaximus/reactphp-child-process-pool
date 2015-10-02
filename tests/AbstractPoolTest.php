<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
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
        $process = \Phake::mock(Process::class);
        \Phake::when($process)->isRunning()->thenReturn(true);
        return $process;
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

    public function testMessage()
    {
        $this->pool->message(Factory::message([
            'foo' => 'bar',
        ]));
        $this->loop->run();
    }

    public function testRpc()
    {
        $this->pool->rpc(Factory::rpc('test', [
            'foo' => 'bar',
        ]));
        $this->loop->run();
    }
}
