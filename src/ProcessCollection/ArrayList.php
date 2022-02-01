<?php

namespace WyriHaximus\React\ChildProcess\Pool\ProcessCollection;

use ArrayIterator;
use WyriHaximus\React\ChildProcess\Pool\ProcessCollectionInterface;

class ArrayList implements ProcessCollectionInterface
{
    /**
     * @var ArrayIterator
     */
    protected $callables;

    public function __construct(array $callables)
    {
        $this->callables = new ArrayIterator($callables);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->callables->current();
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->callables->key();
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->callables->next();
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->callables->rewind();
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->callables->valid();
    }
}
