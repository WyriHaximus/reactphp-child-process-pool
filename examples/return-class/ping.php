<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFixed;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFlexible;
use WyriHaximus\React\ChildProcess\Pool\Factory\Fixed;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

$loop = EventLoopFactory::create();

CpuCoreCountFlexible::createFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)
->then(function (PoolInterface $pool) {
    $promises = [];
    for ($i = 0; $i < 100; $i++) {
        //echo $i, PHP_EOL;

        $promises[] = $pool->rpc(MessagesFactory::rpc('return', [
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
            var_export($pool->info());
        });
    }

    return \React\Promise\all($promises)->then(function () use ($pool) {
        var_export($pool->info());
        return $pool;
    });
})->then(function (PoolInterface $pool) {
    return $pool->terminate(Factory::message(['bye!']));
})->then(function () use ($loop) {
    $loop->stop();
})->done();
$loop->run();
