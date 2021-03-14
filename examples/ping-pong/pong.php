<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\ChildProcess\ArgvEncoder;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

$loop = Factory::create();

if (class_exists('\WyriHaximus\React\ChildProcess\Messenger\ChildProcess\ArgvEncoder')) {
    $arguments = \array_pop($argv);
    $recipient = \WyriHaximus\React\ChildProcess\Messenger\Factory::child($loop, ArgvEncoder::decode($arguments));
} else {
    $recipient = \WyriHaximus\React\ChildProcess\Messenger\Factory::child($loop);
}
\React\Promise\resolve($recipient)->then(function (Messenger $recipient) use ($loop) {
    $recipient->on('message', function (Payload $payload, Messenger $messenger) {
        $messenger->write(json_encode([
            'type' => 'message',
            'payload' => $payload,
        ]));

        $messenger->getLoop()->addTimer(1, function () use ($messenger) {
            $messenger->getLoop()->stop();
        });
    });
    $recipient->registerRpc('ping', function (Payload $payload, Messenger $messenger) use ($loop) {
        $stopAt = time() + mt_rand(1, 2);

        do {
            // Don nothing
        } while ($stopAt >= time());

        /*$messenger->getLoop()->addTimer(1, function () use ($messenger) {
            $messenger->getLoop()->stop();
        });*/

        return \React\Promise\resolve([
            'result' => $payload['i'] * $payload['i'] * $payload['i'] * $payload['i'],
        ]);
    });
});
$loop->run();
