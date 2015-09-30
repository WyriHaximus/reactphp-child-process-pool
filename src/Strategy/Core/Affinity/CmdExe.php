<?php

namespace WyriHaximus\React\ChildProcess\Pool\Strategy\Core\Affinity;

use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Pool\Os;
use WyriHaximus\React\ChildProcess\Pool\Strategy\Core\AffinityInterface;

class CmdExe implements AffinityInterface
{
    /**
     * @return array
     */
    public static function getSupportedOSs()
    {
        return [
            Os::WIN,
        ];
    }

    /**
     * @return string
     */
    public function getCommandName()
    {
        return 'cmd.exe';
    }

    /**
     * @return PromiseInterface
     */
    public function execute($address = 0, $cmd = '')
    {
        return 'cmd.exe /C start /affinity ' . $address . ' ' . $cmd;
    }
}
