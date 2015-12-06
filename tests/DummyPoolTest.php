<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Pool\DummyPool;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;

class DummyPoolTest extends \PHPUnit_Framework_TestCase
{
    protected function getPool()
    {
        return new DummyPool(new Process(''), Factory::create(), []);
    }

    public function testInfo()
    {
        $this->assertEquals([], $this->getPool()->info());
    }

    public function testRpc()
    {
        $this->assertInstanceOf('React\Promise\FulfilledPromise', $this->getPool()->rpc(MessageFactory::rpc('foo', ['bar'])));
    }

    public function testMessage()
    {
        $this->getPool()->message(MessageFactory::message(['foo.nar']));
    }

    public function testTerminate()
    {
        $this->getPool()->terminate('foo.nar');
    }
}
