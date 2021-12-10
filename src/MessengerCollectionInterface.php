<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use WyriHaximus\React\ChildProcess\Messenger\Messenger;

interface MessengerCollectionInterface extends \Iterator
{
    public function current(): Messenger;
}
