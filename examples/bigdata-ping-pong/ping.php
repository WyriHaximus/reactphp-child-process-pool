<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Pool\FixedPool;
use WyriHaximus\React\ChildProcess\Pool\FlexiblePool;

const POOL_PROCESS_COUNT = 64;
//const I = 100000000;
const I = 50000;

echo 'Warning this example can be rather harsh on your hardware, stop now or continue with cation!!!!', PHP_EOL;
echo 'Starting a pool with ' . POOL_PROCESS_COUNT . ' child processes looping from 0 till ' . I . ' and calculating $i * $i * $i * $i in the child process.';
echo PHP_EOL;
/*echo 'Starting in:', PHP_EOL, '5', PHP_EOL;
sleep(1);
echo '4', PHP_EOL;
sleep(1);
echo '3', PHP_EOL;
sleep(1);
echo '2', PHP_EOL;
sleep(1);
echo '1', PHP_EOL;
sleep(1);*/

$bigData = str_pad('', I, 'p');


$poolClass = FixedPool::class;
$poolClass = FlexiblePool::class;

$loop = Factory::create();
$pool = new $poolClass(new Process('exec php ' . dirname(dirname(__DIR__)) . '/examples/bigdata-ping-pong/pong.php'), $loop, [
    //'size' => POOL_PROCESS_COUNT,
    'min_size' => 1,
    'max_size' => 32,
]);


$timer = $loop->addPeriodicTimer(0.1, function () use ($pool) {
    echo 'Pool status: ', PHP_EOL;
    foreach ($pool->info() as $key => $value) {
        echo "\t", $key, ': ', $value, PHP_EOL;
    }
});

$promises = [];

for ($i = 0; $i < POOL_PROCESS_COUNT * 64; $i++) {
    echo $i, PHP_EOL;
    $promises[] = $pool->rpc(MessagesFactory::rpc('ping', [
        'data' => $bigData,
    ]))->then(function ($data) {
        echo 'Return length: ', strlen($data['data']), PHP_EOL;
    }, function ($error) {
        var_export($error);
        die();
    });
}

$pool->rpc(MessagesFactory::rpc('ping', [
    'data' => $bigData,
]))->then(function () use ($pool, $timer) {
    echo 'Terminating pool', PHP_EOL;
    $pool->terminate(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
        'woeufh209h838392',
    ]));
    $timer->cancel();
    echo 'Done!!!', PHP_EOL;
});

$loop->run();
