<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use React\ChildProcess\Process;

interface ProcessCollectionInterface extends \Iterator
{
    /**
     * @return Process
     */
    public function current();

    /**
     * @return mixed
     */
    public function key();

    public function next();

    public function rewind();

    /**
     * @return bool
     */
    public function valid();
}
