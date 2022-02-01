<?php

namespace WyriHaximus\React\ChildProcess\Pool;

interface ProcessCollectionInterface extends \Iterator
{
    /**
     * @return callable
     */
    #[\ReturnTypeWillChange]
    public function current();

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key();

    #[\ReturnTypeWillChange]
    public function next();

    #[\ReturnTypeWillChange]
    public function rewind();

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid();
}
