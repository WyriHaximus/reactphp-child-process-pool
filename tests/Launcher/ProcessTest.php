<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Launcher;

use Phake;
use WyriHaximus\React\ChildProcess\Pool\Launcher\Process;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $childProcess = Phake::mock('React\ChildProcess\Process');
        $startCalled = false;
        Phake::when($childProcess)->start($loop, $this->isType('float'))->thenReturnCallback(function () use (&$startCalled) {
            $startCalled = true;
            return true;
        });
        $process = new Process($childProcess);
        $process($loop, []);
        $this->assertTrue($startCalled);
    }
}
