<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

error_reporting(-1);
ini_set('display_errors', true);
ini_set('error_log', 'err-pong.txt');

$loop = Factory::create();
$recipient = \WyriHaximus\React\ChildProcess\Messenger\Factory::child($loop);
$recipient->on('message', function (Payload $payload, Messenger $messenger) use ($loop, $recipient) {
    trigger_error('An error not interrupting unless caught.');
    try {
        file_put_contents(
            'log-pong.txt',
            'Got msg: '. var_export($payload->getPayload(), true) . PHP_EOL,
            FILE_APPEND
        );
        $messenger->message(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
            'No one seems receiving this.'
        ]));
        $recipient->message(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
            'No one seems receiving this, either.'
        ]));
        $loop->stop();
        throw new Exception('An exception to be caught.');
    } catch (Exception $e) {
        file_put_contents('exc-pong.txt', $e->getMessage() . PHP_EOL, FILE_APPEND);
        $messenger->softTerminate();
    }
    throw new Exception('An exception to become an event.');
});
$recipient->on('error', function (Exception $e) {
    file_put_contents('exc-pong.txt', $e->getMessage() . PHP_EOL, FILE_APPEND);
});
$recipient->registerRpc('ping', function (Payload $payload, Messenger $messenger) use ($loop) {
    $stopAt = time() + mt_rand(1, 2);

    do {
        // Do nothing
    } while ($stopAt >= time());

//    trigger_error('An error to cause infinite loop if not caught.');

    return \React\Promise\resolve([
        'result' => $payload['i'] * $payload['i'] * $payload['i'] * $payload['i'],
    ]);
});

$loop->run();
