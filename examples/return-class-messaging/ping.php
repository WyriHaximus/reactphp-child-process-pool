<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFixed;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFlexible;
use WyriHaximus\React\ChildProcess\Pool\Factory\Fixed;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

$loop = EventLoopFactory::create();

Fixed::createFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)
->then(function (PoolInterface $pool) {
    return $pool->rpc(MessagesFactory::rpc('return', [
        'ping' => true,
    ]))->then(function () use ($pool) {
        return $pool;
    });
})->then(function (PoolInterface $pool) {
    $messageCount = 0;
    $deferred = new Deferred();

    $pool->on('message', function ($message) use (&$messageCount, $deferred) {
        var_export($message);
        $messageCount++;
        if ($messageCount >= 100) {
            $deferred->resolve();
        }
    });
    $pool->on('error', function ($error) {
        echo $error;
    });

    for ($i = 0; $i < 100; $i++) {
        $message = MessagesFactory::message([
            'i' => $i,
            'time' => time(),
            'int' => mt_rand(0, 1000),
        ]);

        var_export($message);

        $pool->message($message);
    }

    return $deferred->promise()->then(function () use ($pool) {
        var_export($pool->info());
        return $pool;
    });
})->then(function (PoolInterface $pool) {
    return $pool->terminate(Factory::message(['bye!']));
})->done();

$loop->run();
