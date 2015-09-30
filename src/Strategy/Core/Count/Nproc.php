<?php

namespace WyriHaximus\React\ChildProcess\Pool\Strategy\Core\Count;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Pool\Os;
use WyriHaximus\React\ChildProcess\Pool\Strategy\Core\CountInterface;

class Nproc implements CountInterface
{
    /**
     * @return array
     */
    public static function getSupportedOSs()
    {
        return [
            Os::LINUX,
        ];
    }

    /**
     * @var LoopInterface
     */
    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @return string
     */
    public function getCommandName()
    {
        return 'nproc';
    }

    /**
     * @return PromiseInterface
     */
    public function execute()
    {
        $deferred = new Deferred();
        $buffer = '';

        $process = new Process('exec nproc');
        $process->on('exit', function ($exitCode) use ($deferred, &$buffer) {
            if ($exitCode == 0) {
                $deferred->resolve($buffer);
                return;
            }

            $deferred->reject();
        });

        \WyriHaximus\React\futurePromise($this->loop, $process)->then(function (Process $process) use (&$buffer) {
            $process->start($this->loop);
            $process->stdout->on('data', function ($output) use (&$buffer) {
                $buffer += $output;
            });
        });

        return $deferred->promise();
    }
}
