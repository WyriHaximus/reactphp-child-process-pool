<?php

namespace WyriHaximus\React\ChildProcess\Pool\ProcessCollection;

use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;

class Single implements ProcessCollectionInterface
{
    protected $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->callable;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return 0;
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        return false;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        // Do nothing
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return true;
    }
}
