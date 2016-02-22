<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool;

use Phake;
use React\ChildProcess\Process;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Pool\Manager\Fixed;
use WyriHaximus\React\ChildProcess\Pool\Manager\Flexible;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\Queue\Memory;

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

    public function providerGetQueue()
    {
        $r = [];

        $r[] = [
            [],
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            Factory::create(),
            new Memory(),
        ];

        $r[] = [
            [
                Options::QUEUE => new Memory(),
            ],
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            Factory::create(),
            new Memory(),
        ];

        $r[] = [
            [
                Options::QUEUE => 'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            ],
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            Factory::create(),
            new Memory(),
        ];

        $mock = Phake::mock('WyriHaximus\React\ChildProcess\Pool\QueueInterface');
        $r[] = [
            [
                Options::QUEUE => $mock,
            ],
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            Factory::create(),
            $mock,
        ];

        return $r;
    }

    /**
     * @dataProvider providerGetQueue
     */
    public function testGetQueue($options, $default, $loop, $output)
    {
        $this->assertEquals(
            $output,
            \WyriHaximus\React\ChildProcess\Pool\getQueue(
                $options,
                $default,
                $loop
            )
        );
    }

    public function providerGetManager()
    {
        $r = [];

        $r[] = [
            [
                Options::SIZE => 0,
            ],
            Phake::mock('WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface'),
            'WyriHaximus\React\ChildProcess\Pool\Manager\Fixed',
            Factory::create(),
            new Fixed(
                Phake::mock('WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface'),
                Factory::create(),
                [
                    Options::SIZE => 0,
                ]
            ),
        ];

        $mock = Phake::mock('WyriHaximus\React\ChildProcess\Pool\ManagerInterface');
        $r[] = [
            [
                Options::MANAGER => $mock,
                Options::SIZE => 0,
            ],
            Phake::mock('WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface'),
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            Factory::create(),
            $mock,
        ];

        $processCollection = Phake::mock('WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface');
        $r[] = [
            [
                Options::MANAGER => new Flexible(
                    $processCollection,
                    Factory::create(),
                    [
                        Options::MIN_SIZE => 0,
                        Options::MAX_SIZE => 0,
                    ]
                ),
                Options::SIZE => 0,
            ],
            Phake::mock('WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface'),
            'WyriHaximus\React\ChildProcess\Pool\Queue\Memory',
            Factory::create(),
            new Flexible(
                $processCollection,
                Factory::create(),
                [
                    Options::MIN_SIZE => 0,
                    Options::MAX_SIZE => 0,
                ]
            ),
        ];

        return $r;
    }

    /**
     * @dataProvider providerGetManager
     */
    public function testGetManager($options, $processCollection, $default, $loop, $output)
    {
        $this->assertEquals(
            $output,
            \WyriHaximus\React\ChildProcess\Pool\getManager(
                $options,
                $processCollection,
                $default,
                $loop
            )
        );
    }

    public function testDetectCoreCount()
    {
        $coreCountDetected = false;
        $loop = Factory::create();

        \WyriHaximus\React\ChildProcess\Pool\detectCoreCount(
            $loop,
            []
        )->then(function ($count) use (&$coreCountDetected) {
            $this->assertInternalType('integer', $count);
            $coreCountDetected = true;
        });

        $loop->run();

        $this->assertTrue($coreCountDetected);
    }

    public function testDetectCoreCountCustomDetector()
    {
        $loop = Factory::create();
        $this->assertSame(
            128,
            \WyriHaximus\React\ChildProcess\Pool\detectCoreCount(
                $loop,
                [
                    Options::DETECTOR => function (LoopInterface $passedLoop) use ($loop)
                    {
                        $this->assertSame($passedLoop, $loop);
                        return 128;
                    }
                ]
            )
        );
    }

    public function testRebuildProcessAndGetProcessPropertyValue()
    {
        $process = new Process(
            'a',
            'b',
            [
                'c'
            ],
            [
                'd'
            ]
        );
        $rebuildProcess = \WyriHaximus\React\ChildProcess\Pool\rebuildProcess(
            13,
            $process
        );
        $this->assertSame('taskset -c 13 a', \WyriHaximus\React\ChildProcess\Pool\getProcessPropertyValue('cmd', $rebuildProcess));
        $this->assertSame('b', \WyriHaximus\React\ChildProcess\Pool\getProcessPropertyValue('cwd', $rebuildProcess));
        $this->assertSame(
            [
                'c',
            ],
            \WyriHaximus\React\ChildProcess\Pool\getProcessPropertyValue('env', $rebuildProcess)
        );
        $this->assertSame(
            [
                'd',
            ],
            \WyriHaximus\React\ChildProcess\Pool\getProcessPropertyValue('options', $rebuildProcess)
        );
    }
}
