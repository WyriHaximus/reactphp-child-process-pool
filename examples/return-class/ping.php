<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\Pool\CpuCoreCountFixed;
use WyriHaximus\React\ChildProcess\Pool\Pool\CpuCoreCountFlexible;
use WyriHaximus\React\ChildProcess\Pool\Pool\Fixed;
use WyriHaximus\React\ChildProcess\Pool\Pool\Flexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

$loop = EventLoopFactory::create();

//Fixed::createFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop);
Flexible::createFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)
->then(function (PoolInterface $pool) {
    $i = 0;
    for ($i = 0; $i < 100; $i++) {
        //echo $i, PHP_EOL;

        $pool->rpc(MessagesFactory::rpc('return', [
            'i' => $i,
            'time' => time(),
            'string' => str_pad('0', 1024 * 1024 * 5)
            //'string' => str_pad('0', 5)
        ]))->then(function (Payload $payload) use ($pool) {
            echo $payload['i'], PHP_EOL;
            echo $payload['time'], PHP_EOL;
            if ($payload['i'] == 99) {
                $pool->terminate();
            }
        });
    }
});
$loop->run();
