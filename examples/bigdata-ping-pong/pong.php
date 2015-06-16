<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

$loop = Factory::create();

$recipient = \WyriHaximus\React\ChildProcess\Messenger\Factory::child($loop);

$recipient->registerRpc('ping', function (Payload $payload, Deferred $deferred) use ($loop) {
    $deferred->resolve([
        'data' => $payload['data'] . $payload['data'] . $payload['data'] . $payload['data'] . $payload['data'] . $payload['data'] . $payload['data'] . $payload['data'] . $payload['data'] . $payload['data'],
    ]);
});

$recipient->on('message', function (Payload $payload, Messenger $messenger) {
    $messenger->write(json_encode([
        'type' => 'message',
        'payload' => $payload,
    ]));

    $messenger->getLoop()->addTimer(1, function () use ($messenger) {
        $messenger->getLoop()->stop();
    });
});

$loop->run();
