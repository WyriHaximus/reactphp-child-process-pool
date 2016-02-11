<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Queue;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Pool\QueueInterface;

trait QueueTestTrait
{
    /**
     * @return QueueInterface
     */
    abstract protected function getQueue();

    public function testInterface()
    {
        $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\QueueInterface', $this->getQueue());
    }

    public function queueProvider()
    {
        $queue = $this->getQueue();
        return array_pad([], 25, [
            $queue,
        ]);
    }

    /**
     * @dataProvider queueProvider
     */
    public function testOperations(QueueInterface $queue)
    {
        $rpc0 = Factory::rpc('a', ['b']);
        $rpc1 = Factory::rpc('c', ['d']);
        $rpc2 = Factory::rpc('e', ['f']);
        $this->assertSame(0, $queue->count());
        $queue->enqueue($rpc0);
        $this->assertSame(1, $queue->count());
        $queue->enqueue($rpc1);
        $this->assertSame(2, $queue->count());
        $queue->enqueue($rpc2);
        $this->assertSame(3, $queue->count());
        $this->assertSame($rpc0, $queue->dequeue());
        $this->assertSame(2, $queue->count());
        $this->assertSame($rpc1, $queue->dequeue());
        $this->assertSame(1, $queue->count());
        $this->assertSame($rpc2, $queue->dequeue());
        $this->assertSame(0, $queue->count());
    }
}
