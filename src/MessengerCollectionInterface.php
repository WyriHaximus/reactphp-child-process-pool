<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use WyriHaximus\React\ChildProcess\Messenger\Messenger;

interface MessengerCollectionInterface extends \Iterator
{
    /**
     * @return Messenger
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
