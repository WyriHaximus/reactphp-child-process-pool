<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\FixedPool;

const POOL_PROCESS_COUNT = 350;
const I = 12345;

echo 'Warning this example can be rather harsh on your hardware, stop now or continue with cation!!!!', PHP_EOL;
echo 'Starting a pool with ' . POOL_PROCESS_COUNT . ' child processes looping from 0 till ' . I . ' and calculating $i * $i * $i * $i in the child process.';
echo PHP_EOL;
echo 'Starting in:', PHP_EOL, '5', PHP_EOL;
sleep(1);
echo '4', PHP_EOL;
sleep(1);
echo '3', PHP_EOL;
sleep(1);
echo '2', PHP_EOL;
sleep(1);
echo '1', PHP_EOL;
sleep(1);

$loop = Factory::create();
$pool = new FixedPool(new Process('exec php ' . dirname(dirname(__DIR__)) . '/examples/ping-pong/pong.php'), $loop, POOL_PROCESS_COUNT);

for ($i = 0; $i < I; $i++) {
    echo $i, PHP_EOL;
    $j = $i;
    $pool->rpc(new Call('ping', new Payload([
        'i' => $i,
    ])))->then(function ($data) use ($j) {
        echo 'Answer for ' . $j . ' * ' . $j . ' * ' . $j . ' * ' . $j . ': ', $data, PHP_EOL;
    }, function ($error) {
        var_export($error);
        die();
    });
}
$pool->rpc(new Call('ping', new Payload([
    'i' => ++$i,
])))->then(function () use ($pool) {
    $pool->terminate();
});

$loop->run();
