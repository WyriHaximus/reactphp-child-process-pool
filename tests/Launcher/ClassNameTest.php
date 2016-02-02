<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Launcher;

use Phake;
use WyriHaximus\React\ChildProcess\Pool\Launcher\ClassName;

class ClassNameTest extends \PHPUnit_Framework_TestCase
{
    public function testClassName()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $timerCalled = false;
        Phake::when($loop)->addPeriodicTimer($this->isType('float'), $this->isType('callable'))->thenReturnCallback(function () use (&$timerCalled) {
            $timerCalled = true;
            return true;
        });
        $process = new ClassName('WyriHaximus\React\ChildProcess\Messenger\ReturnChild');
        $process($loop, []);
        $this->assertTrue($timerCalled);
    }
}
