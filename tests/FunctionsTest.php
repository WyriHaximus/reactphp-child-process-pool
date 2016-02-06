<?php

use WyriHaximus\React\ChildProcess\Pool\Options;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function provideGetClassNameFromOptionOrDefault()
    {
        $r = [];

        $r[] = [
            [],
            Options::MANAGER,
            '',
            'foo.bar',
            'foo.bar',
        ];

        $r[] = [
            [
                Options::MANAGER => 'stdClass',
            ],
            Options::MANAGER,
            'stdClass',
            'foo.bar',
            'stdClass',
        ];

        $r[] = [
            [
                Options::MANAGER => 'WyriHaximus\React\ChildProcess\Pool\Manager\Fixed',
            ],
            Options::MANAGER,
            'WyriHaximus\React\ChildProcess\Pool\ManagerInterface',
            'foo.bar',
            'WyriHaximus\React\ChildProcess\Pool\Manager\Fixed',
        ];

        return $r;
    }

    /**
     * @dataProvider provideGetClassNameFromOptionOrDefault
     */
    public function testGetClassNameFromOptionOrDefault($options, $key, $instanceOf, $fallback, $output)
    {
        $this->assertSame(
            $output,
            \WyriHaximus\React\ChildProcess\Pool\getClassNameFromOptionOrDefault(
                $options,
                $key,
                $instanceOf,
                $fallback
            )
        );
    }
}
