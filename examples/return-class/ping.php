<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\Pool\Fixed;

$loop = EventLoopFactory::create();

$pool = Fixed::createFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop);

$i = 0;
for ($i = 0; $i < 100; $i++) {
    echo $i, PHP_EOL;

    $pool->rpc(MessagesFactory::rpc('return', [
        'i' => $i,
        'time' => time(),
        'string' => str_pad('0', 1024 * 1024 * 5)
    ]))->then(function (Payload $payload) use ($pool) {
        echo $payload['i'], PHP_EOL;
        echo $payload['time'], PHP_EOL;
        if ($payload['i'] == 99) {
            $pool->terminate();
        }
    });
}

$loop->run();
