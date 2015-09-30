<?php

namespace WyriHaximus\React\ChildProcess\Pool\Strategy\Core;

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Pool\Strategy\CoreInterface;

interface CountInterface extends CoreInterface
{
    public function __construct(LoopInterface $loop);
}
