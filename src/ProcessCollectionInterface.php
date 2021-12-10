<?php

namespace WyriHaximus\React\ChildProcess\Pool;

interface ProcessCollectionInterface extends \Iterator
{
    public function current(): callable;
}
