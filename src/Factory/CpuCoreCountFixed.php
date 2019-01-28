<?php

namespace WyriHaximus\React\ChildProcess\Pool\Factory;

use React\ChildProcess\Process as ChildProcess;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\CpuCoreDetector\Detector;
use WyriHaximus\CpuCoreDetector\Resolver;
use WyriHaximus\FileDescriptors\Factory;
use WyriHaximus\FileDescriptors\NoCompatibleListerException;
use WyriHaximus\React\ChildProcess\Pool\Launcher\ClassName;
use WyriHaximus\React\ChildProcess\Pool\Launcher\Process;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\Pool\Fixed;
use WyriHaximus\React\ChildProcess\Pool\PoolFactoryInterface;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollection\ArrayList;

class CpuCoreCountFixed implements PoolFactoryInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected static $defaultOptions = [
        Options::SIZE => 25,
    ];

    /**
     * @param ChildProcess $process
     * @param LoopInterface $loop
     * @param array $options
     * @return PromiseInterface
     */
    public static function create(ChildProcess $childProcess, LoopInterface $loop, array $options = [])
    {
        $options = array_merge(self::$defaultOptions, $options);
        try {
            if (!isset($options[Options::FD_LISTER])) {
                $options[Options::FD_LISTER] = Factory::create();
            }
        } catch (NoCompatibleListerException $exception) {
            // Do nothing, platform unsupported
        }
        return \WyriHaximus\React\ChildProcess\Pool\detectCoreCount(
            $loop,
            $options
        )->then(function ($coreCount) use ($childProcess, $loop, $options) {
            $options[Options::SIZE] = $coreCount;
            $processes = [];
            for ($i = 0; $i < $coreCount; $i++) {
                $processes[] = \WyriHaximus\React\ChildProcess\Pool\rebuildProcess(
                    $i,
                    $childProcess
                )->then(function (ChildProcess $process) {
                    return \React\Promise\resolve(new Process($process));
                });
            }

            return \React\Promise\all($processes)->then(function ($processes) use ($loop, $options) {
                return  \React\Promise\resolve(new Fixed(new ArrayList($processes), $loop, $options));
            });
        });
    }

    /**
     * @param string $class
     * @param LoopInterface $loop
     * @param array $options
     * @return PromiseInterface
     */
    public static function createFromClass($class, LoopInterface $loop, array $options = [])
    {
        $options = array_merge(self::$defaultOptions, $options);
        try {
            if (!isset($options[Options::FD_LISTER])) {
                $options[Options::FD_LISTER] = Factory::create();
            }
        } catch (NoCompatibleListerException $exception) {
            // Do nothing, platform unsupported
        }
        return \WyriHaximus\React\ChildProcess\Pool\detectCoreCount(
            $loop,
            $options
        )->then(function ($coreCount) use ($class, $loop, $options) {
            $options[Options::SIZE] = $coreCount;
            $processes = [];
            for ($i = 0; $i < $coreCount; $i++) {
                $processes[] = Resolver::resolve($i, '%s')->then(function ($command) use ($class, $options) {
                    $classNameOptions = [
                        'cmdTemplate' => $command,
                    ];

                    if (isset($options[Options::FD_LISTER])) {
                        $classNameOptions[Options::FD_LISTER] = $options[Options::FD_LISTER];
                    }

                    return \React\Promise\resolve(new ClassName($class, $classNameOptions));
                });
            }

            return \React\Promise\all($processes)->then(function ($processes) use ($loop, $options) {
                return  \React\Promise\resolve(new Fixed(new ArrayList($processes), $loop, $options));
            });
        });
    }
}
