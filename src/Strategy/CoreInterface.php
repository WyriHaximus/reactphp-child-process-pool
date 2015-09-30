<?php

namespace WyriHaximus\React\ChildProcess\Pool\Strategy;

use WyriHaximus\React\ChildProcess\Pool\StrategyInterface;

interface CoreInterface extends StrategyInterface
{
    /**
     * @return string
     */
    public function getCommandName();
}
