<?php

namespace WyriHaximus\React\ChildProcess\Pool\Strategy\Core\Affinity;

use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Pool\Os;
use WyriHaximus\React\ChildProcess\Pool\Strategy\Core\AffinityInterface;

class Taskset implements AffinityInterface
{
    /**
     * @return array
     */
    public static function getSupportedOSs()
    {
        return [
            Os::LINUX,
        ];
    }

    /**
     * @return string
     */
    public function getCommandName()
    {
        return 'taskset';
    }

    /**
     * @return PromiseInterface
     */
    public function execute($address = 0, $cmd = '')
    {
        return 'taskset -c ' . $address . ' ' . $cmd;
    }
}
