<?php

namespace WyriHaximus\React\ChildProcess\Pool;

class Os
{
    const UNKNOWN = 0;
    const LINUX = 1;
    const OSX = 2;
    const WIN = 13;

    /**
     * @return int
     */
    public static function getOS()
    {
        switch (true) {
            case stristr(PHP_OS, 'LINUX'):
                return self::LINUX;
            case stristr(PHP_OS, 'DAR'):
                return self::OSX;
            case stristr(PHP_OS, 'WIN'):
                return self::WIN;
            default:
                return self::OS_UNKNOWN;
        }
    }
}
