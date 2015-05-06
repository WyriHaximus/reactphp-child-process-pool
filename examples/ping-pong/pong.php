<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

$loop = Factory::create();

(new Recipient($loop))->registerRpc('ping', function (Invoke $invoke) use ($loop) {
    $i = $invoke->getPayload()['i'];
    $invoke->getDeferred()->resolve($i * $i * $i * $i);
});

$loop->run();
