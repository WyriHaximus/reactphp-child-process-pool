<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

const POOL_PROCESS_COUNT = 4;
const I = 3;

$loop = Factory::create();
Flexible::create(
    new Process('php '. dirname(dirname(__DIR__)) .'/examples/ping-pong-with-error/pong.php'),
    $loop,
    [ 'min_size' => 1, 'max_size' => POOL_PROCESS_COUNT ]
)->then(function (PoolInterface $pool) use ($loop) {
    $pool->on('message', function ($message) {
        var_export($message);
    });

    $pool->on('error', function ($e) {
        echo 'Error: ', var_export($e, true), PHP_EOL;
    });

    for ($i = 0; $i < I; $i++) {
        echo "$i ";
        $j = $i;
        $pool->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('ping', [
            'i' => $i,
        ]))->then(function ($data) use ($j) {
            echo "Answer for $j * $j * $j * $j: ", $data['result'], PHP_EOL;
        }, function ($error) {
            var_export($error);
            die();
        });
    }
    echo PHP_EOL;

    $timer = $loop->addPeriodicTimer(0.5, function () use ($pool) {
        $status = 'Pool status: ';
        foreach ($pool->info() as $key => $value) {
            $status .= "    $key: $value";
        }
        echo $status, PHP_EOL;
    });

    $loop->addTimer(2, function () use ($pool, $timer, $loop) {
        for ($i = 0; $i < I; $i++) {
            echo "$i ";
            $j = $i;
            $pool->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('ping', [
                'i' => $i,
            ]))->then(function ($data) use ($j) {
                echo "Answer for $j * $j * $j * $j: ", $data['result'], PHP_EOL;
            }, function ($error) {
                var_export($error);
                die();
            });
        }
        echo PHP_EOL;

        $pool->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('ping', [
            'i' => ++$i,
        ]))->then(function () use ($pool, $timer, $loop) {
            echo 'Terminating pool', PHP_EOL;
            $pool->terminate(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
                'woeufh209h838392',
            ]));
            $timer->cancel();
            echo 'Done!!!', PHP_EOL;
        });
    });
});

$loop->run();
