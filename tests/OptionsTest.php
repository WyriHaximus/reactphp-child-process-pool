<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testNoMethods()
    {
        $this->assertSame(0, count(get_class_methods('WyriHaximus\React\ChildProcess\Pool\Options')));
    }

    public function testNoVars()
    {
        $this->assertSame(0, count(get_class_vars('WyriHaximus\React\ChildProcess\Pool\Options')));
    }
}
