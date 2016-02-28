<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Pool;

use Phake;
use React\ChildProcess\Process;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\Pool\Dummy;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $poolPromise = Dummy::create(
            new Process(
                'a',
                'b',
                [
                    'c'
                ],
                [
                    'd'
                ]
            ),
            Phake::mock('React\EventLoop\LoopInterface')
        );

        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\Pool\Dummy', $pool);
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\PoolInterface', $pool);
            $promiseHasResolved = true;
        });
        $this->assertTrue($promiseHasResolved);
    }

    public function testCreateFromClass()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = Dummy::createFromClass('stdClass', $loop);

        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\Pool\Dummy', $pool);
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\PoolInterface', $pool);
            $promiseHasResolved = true;
        });
        $this->assertTrue($promiseHasResolved);
    }

    public function testMethods()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = Dummy::createFromClass('stdClass', $loop);

        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved) {
            $pool->message(Factory::message([]));
            $pool->terminate(Factory::message([]));

            $this->assertInstanceOf('React\Promise\FulfilledPromise', $pool->rpc(Factory::rpc('abc', ['def'])));
            $this->assertSame([], $pool->info());
            $promiseHasResolved = true;
        });
        $this->assertTrue($promiseHasResolved);
    }
}
