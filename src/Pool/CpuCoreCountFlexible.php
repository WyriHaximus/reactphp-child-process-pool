<?php

namespace WyriHaximus\React\ChildProcess\Pool\Pool;

use React\ChildProcess\Process as ChildProcess;
use React\EventLoop\LoopInterface;
use WyriHaximus\CpuCoreDetector\Detector;
use WyriHaximus\CpuCoreDetector\Resolver;
use WyriHaximus\React\ChildProcess\Pool\Launcher\ClassName;
use WyriHaximus\React\ChildProcess\Pool\Launcher\Process;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollection\ArrayList;

class CpuCoreCountFlexible
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected static $defaultOptions =[
        Options::MIN_SIZE => 0,
        Options::MAX_SIZE => 1,
    ];

    public static function create(ChildProcess $childProcess, LoopInterface $loop, array $options = [])
    {
        $options = array_merge(self::$defaultOptions, $options);
        return \WyriHaximus\React\ChildProcess\Pool\detectCoreCount(
            $loop,
            $options
        )->then(function ($coreCount) use ($childProcess, $loop, $options) {
            $options[Options::MAX_SIZE] = $coreCount;
            $array = [];
            for ($i = 0; $i < $coreCount; $i++) {
                $array[] = new Process(
                    \WyriHaximus\React\ChildProcess\Pool\rebuildProcess(
                        $i,
                        $childProcess
                    )
                );
            }
            return \React\Promise\resolve(new Flexible(new ArrayList($array), $loop, $options));
        });
    }

    public static function createFromClass($class, LoopInterface $loop, array $options = [])
    {
        $options = array_merge(self::$defaultOptions, $options);
        return \WyriHaximus\React\ChildProcess\Pool\detectCoreCount(
            $loop,
            $options
        )->then(function ($coreCount) use ($class, $loop, $options) {
            $options[Options::MAX_SIZE] = $coreCount;
            $array = [];
            for ($i = 0; $i < $coreCount; $i++) {
                $array[] = new ClassName($class, [
                    'cmdTemplate' => Resolver::resolve($i, '%s'),
                ]);
            }
            return \React\Promise\resolve(new Flexible(new ArrayList($array), $loop, $options));
        });
    }
}