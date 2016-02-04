<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\ProcessCollection;

use Phake;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\RejectedPromise;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\Manager\Fixed;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;
use WyriHaximus\React\ChildProcess\Pool\WorkerInterface;

class FixedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessCollectionInterface
     */
    protected $processCollection;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Fixed
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->processCollection = Phake::mock('WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface');
        $this->loop = Phake::mock('React\EventLoop\LoopInterface');
    }

    protected function noWorkers()
    {
        Phake::when($this->processCollection)->current()->thenReturnCallback(function () {
            return function () {
                return new RejectedPromise();
            };
        });
    }

    protected function createManager()
    {
        $this->manager = new Fixed($this->processCollection, $this->loop, [
            'size' => 1,
        ]);
    }

    public function tearDown()
    {
        $this->manager           = null;
        $this->loop              = null;
        $this->processCollection = null;

        parent::tearDown();
    }

    public function testInfoAtStart()
    {
        $this->noWorkers();
        $this->createManager();

        $this->assertSame([
            'total' => 0,
            'busy' => 0,
            'idle' => 0,
        ], $this->manager->info());
    }

    public function testPingEmit()
    {
        Phake::when($this->processCollection)->current()->thenReturnCallback(function () {
            return function () {
                return new FulfilledPromise(Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger'));
            };
        });

        $this->createManager();

        $called = false;
        $this->manager->once('ready', function (WorkerInterface $worker) use (&$called) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\WorkerInterface', $worker);
            $called = true;
        });
        $this->manager->ping();

        $this->assertTrue($called);
    }

    public function testRpc()
    {
        $rpc = Factory::rpc('foo', ['bar']);
        $workerDeferred = new Deferred();
        $rpcDeferred = new Deferred();
        $worker = null;
        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->rpc($rpc)->thenReturn($rpcDeferred->promise());

        Phake::when($this->processCollection)->current()->thenReturnCallback(function () use ($workerDeferred) {
            return function () use ($workerDeferred) {
                return $workerDeferred->promise();
            };
        });

        $this->createManager();

        $this->manager->once('ready', function (WorkerInterface $workerInstance) use (&$worker) {
            $worker = $workerInstance;
        });

        $workerDeferred->resolve($messenger);

        $this->assertSame([
            'total' => 1,
            'busy' => 0,
            'idle' => 1,
        ], $this->manager->info());

        $worker->rpc($rpc);

        $this->assertSame([
            'total' => 1,
            'busy' => 1,
            'idle' => 0,
        ], $this->manager->info());

        $rpcDeferred->resolve();

        $this->assertSame([
            'total' => 1,
            'busy' => 0,
            'idle' => 1,
        ], $this->manager->info());
    }

    public function testTerminate()
    {
        $workerDeferred = new Deferred();
        $worker = null;
        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');

        Phake::when($this->processCollection)->current()->thenReturnCallback(function () use ($workerDeferred) {
            return function () use ($workerDeferred) {
                return $workerDeferred->promise();
            };
        });

        $this->createManager();

        $this->manager->once('ready', function (WorkerInterface $workerInstance) use (&$worker) {
            $worker = $workerInstance;
        });

        $workerDeferred->resolve($messenger);

        $this->assertSame([
            'total' => 1,
            'busy' => 0,
            'idle' => 1,
        ], $this->manager->info());

        $emittedTerminate = false;
        $worker->on('terminating', function ($worker) use (&$emittedTerminate) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\WorkerInterface', $worker);
            $emittedTerminate = true;
        });

        $this->manager->terminate();

        $this->assertTrue($emittedTerminate);
        Phake::verify($messenger)->softTerminate();
    }
}
