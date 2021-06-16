<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Pool\Factory\CpuCoreCountFlexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

const POOL_PROCESS_COUNT = 10;
const I = 512;

echo 'Warning this example can be rather harsh on your hardware, stop now or continue with cation!!!!', PHP_EOL;
//echo 'Starting a pool with ' . POOL_PROCESS_COUNT . ' child processes looping from 0 till ' . I . ' and calculating $i * $i * $i * $i in the child process.';
//echo PHP_EOL;
/*(echo 'Starting in:', PHP_EOL, '5', PHP_EOL;
sleep(1);
echo '4', PHP_EOL;
sleep(1);
echo '3', PHP_EOL;
sleep(1);
echo '2', PHP_EOL;
sleep(1);
echo '1', PHP_EOL;
sleep(1);*/

$loop = Factory::create();
CpuCoreCountFlexible::create(new Process('php ' . dirname(dirname(__DIR__)) . '/examples/ping-pong/pong.php'), $loop)->then(function (PoolInterface $pool) use ($loop) {
    $pool->on('message', function ($message) {
        var_export($message);
    });

    $pool->on('error', function ($e) {
        echo 'Error: ', var_export($e, true), PHP_EOL;
    });

    for ($i = 0; $i < I; $i++) {
        echo $i, PHP_EOL;
        $j = $i;
        $pool->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('ping', [
            'i' => $i,
            's' => str_pad('', 512, '.'),
        ]))->then(function ($data) use ($j) {
            echo 'Answer for ' . $j . ' * ' . $j . ' * ' . $j . ' * ' . $j . ': ', $data['result'], PHP_EOL;
        }, function ($error) {
            var_export($error);
            die();
        });
    }

    $timer = $loop->addPeriodicTimer(0.1, function () use ($pool) {
        echo 'Pool status: ', PHP_EOL;
        foreach ($pool->info() as $key => $value) {
            echo "\t", $key, ': ', $value, PHP_EOL;
        }
    });

    $loop->addTimer(10, function () use ($pool, $timer, $loop) {
        for ($i = 0; $i < I; $i++) {
            echo $i, PHP_EOL;
            $j = $i;
            $pool->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('ping', [
                'i' => $i,
                's' => str_pad('', 512, '.'),
            ]))->then(function ($data) use ($j) {
                echo 'Answer for ' . $j . ' * ' . $j . ' * ' . $j . ' * ' . $j . ': ', $data['result'], PHP_EOL;
            }, function ($error) {
                var_export($error);
                die();
            });
        }

        $pool->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('ping', [
            'i' => ++$i,
        ]))->then(function () use ($pool, $timer, $loop) {
            echo 'Terminating pool', PHP_EOL;
            $pool->terminate(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
                'woeufh209h838392',
            ]));
            $loop->cancelTimer($timer);
            echo 'Done!!!', PHP_EOL;
        });
    });
});

$loop->run();
