<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Queue;

use WyriHaximus\React\ChildProcess\Pool\Queue\Memory;

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    use QueueTestTrait;

    protected function getQueue()
    {
        return new Memory();
    }
}
