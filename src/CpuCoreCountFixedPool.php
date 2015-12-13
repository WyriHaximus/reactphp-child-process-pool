<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Pool\Strategy\Core\Affinity\Taskset;
use WyriHaximus\React\ChildProcess\Pool\Strategy\Core\AffinityInterface;
use WyriHaximus\React\ChildProcess\Pool\Strategy\Core\Count\Nproc;
use WyriHaximus\React\ChildProcess\Pool\Strategy\Core\CountInterface;

class CpuCoreCountFixedPool extends FixedPool implements PoolInterface
{
    use CpuCoreCountTrait;

    const INTERVAL = 0.01;

    /**
     * @var \ReflectionClass
     */
    protected $sourceProcess;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var array
     */
    protected $options = [
        'size' => 2,
        'strategies' => [
            'core' => [
                'affinity' => null,
                'count' => null,
            ],
        ],
    ];

    /**
     * @var \SplQueue
     */
    protected $readyPool;

    /**
     * @var \SplObjectStorage
     */
    protected $pool;

    /**
     * @var \SplQueue
     */
    protected $callQueue;

    /**
     * @var null|TimerInterface
     */
    protected $timer;

    /**
     * @param Process $process
     * @param LoopInterface $loop
     * @param array $options
     */
    public function __construct(Process $process, LoopInterface $loop, array $options = [])
    {
        $this->sourceProcess = $process;
        $this->loop = $loop;
        $this->options = array_merge($this->options, $options);

        $this->setUpStrategies();

        $this->readyPool = new \SplQueue();
        $this->pool = new \SplObjectStorage();

        $this->callQueue = new \SplQueue();
        $this->detectCoreCount()->then(function ($coreCount) {
            $this->options['size'] = $coreCount;
            return $this->resolveCoreAddresses($coreCount);
        })->then(function ($addresses) {
            foreach ($addresses as $address) {
                $this->spawnProcessAtAddress($address);
            }
        });
    }
}
