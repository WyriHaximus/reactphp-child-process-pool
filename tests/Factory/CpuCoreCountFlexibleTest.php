<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Factory;

use Phake;
use React\ChildProcess\Process;
use React\Promise\FulfilledPromise;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFlexible;

class CpuCoreCountFlexibleTest extends \PHPUnit_Framework_TestCase
{
    protected function createProcess()
    {
        return new Process(
            'a',
            'b',
            [
                'c'
            ],
            [
                'd'
            ]
        );
    }

    public function testCreate()
    {
        $process = $this->createProcess();
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = CpuCoreCountFlexible::create($process, $loop, [
            Options::DETECTOR => function ($loop) {
                return new FulfilledPromise(4);
            },
        ]);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolPromise);
        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\Pool\Flexible', $pool);
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\PoolInterface', $pool);
            $promiseHasResolved = true;
        });
        $this->assertTrue($promiseHasResolved);
    }

    public function testCreateFromClass()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = CpuCoreCountFlexible::createFromClass('stdClass', $loop, [
            Options::DETECTOR => function ($loop) {
                return new FulfilledPromise(4);
            },
            Options::MIN_SIZE => 1,
            Options::MAX_SIZE => 1,
        ]);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolPromise);
        $promiseHasResolved = false;
        $poolPromise->then(null, function ($exception) use (&$promiseHasResolved) {
            $this->assertInstanceOf('Exception', $exception);
            $this->assertSame('Given class doesn\'t implement ChildInterface', $exception->getMessage());
            $promiseHasResolved = true;
        });
        $this->assertTrue($promiseHasResolved);
    }
}
