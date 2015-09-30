<?php

namespace WyriHaximus\React\ChildProcess\Pool\Strategy\Detector;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use WyriHaximus\React\ChildProcess\Pool\Os;
use WyriHaximus\React\ChildProcess\Pool\Strategy\DetectorInterface;

class Hash implements DetectorInterface
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
     * @return PromiseInterface
     */
    public function execute($program = '')
    {
        if ($program === '') {
            return new RejectedPromise();
        }

        $deferred = new Deferred();

        $process = new Process('exec hash ' . $program);
        $process->on('exit', function ($exitCode) use ($deferred) {
            if ($exitCode == 0) {
                $deferred->resolve();
                return;
            }

            $deferred->reject();
        });

        \WyriHaximus\React\futurePromise($this->loop, $process)->then(function (Process $process) {
            $process->start($this->loop);
        });

        return $deferred->promise();
    }
}
