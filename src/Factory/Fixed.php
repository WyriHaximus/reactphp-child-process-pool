<?php

namespace WyriHaximus\React\ChildProcess\Pool\Factory;

use React\ChildProcess\Process as ChildProcess;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\FileDescriptors\Factory;
use WyriHaximus\FileDescriptors\NoCompatibleListerException;
use WyriHaximus\React\ChildProcess\Pool\Launcher\ClassName;
use WyriHaximus\React\ChildProcess\Pool\Launcher\Process;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\PoolFactoryInterface;
use WyriHaximus\React\ChildProcess\Pool\Pool\Fixed as FixedPool;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollection\Single;

class Fixed implements PoolFactoryInterface
{
    /**
     * @var array
     */
    protected static $defaultOptions =[
        Options::SIZE => 5,
    ];

    /**
     * @param ChildProcess $process
     * @param LoopInterface $loop
     * @param array $options
     * @return PromiseInterface
     */
    public static function create(ChildProcess $process, LoopInterface $loop, array $options = [])
    {
        $options = array_merge(self::$defaultOptions, $options);
        try {
            if (!isset($options[Options::FD_LISTER])) {
                $options[Options::FD_LISTER] = Factory::create();
            }
        } catch (NoCompatibleListerException $exception) {
            // Do nothing, platform unsupported
        }
        return \React\Promise\resolve(new FixedPool(new Single(new Process($process)), $loop, $options));
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
        return \React\Promise\resolve(new FixedPool(new Single(new ClassName($class)), $loop, $options));
    }
}
