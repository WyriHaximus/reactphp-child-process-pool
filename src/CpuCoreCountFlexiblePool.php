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

class CpuCoreCountFlexiblePool extends FlexiblePool implements PoolInterface
{
    use CpuCoreCountTrait;

    const INTERVAL = 0.01;

    /**
     * @var bool
     */
    protected $detectingCores = true;

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
        'min_size' => 0,
        'max_size' => 1,
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
        $this->detectingCores = true;
        $this->detectCoreCount()->then(function ($coreCount) {
            $this->options['max_size'] = $coreCount;
            if ($this->options['min_size'] > $this->options['max_size']) {
                $this->options['min_size'] = $this->options['max_size'];
            }
            return $this->resolveCoreAddresses($coreCount);
        })->then(function ($addresses) {
            $this->detectingCores = false;
            foreach ($addresses as $address) {
                $this->spawnProcessAtAddress($address);
            }
        });
    }

    protected function shouldShutDownMessenger(Messenger $messenger)
    {
        if ($this->callQueue->count() == 0 && $this->pool->count() > $this->options['min_size']) {
            unset($this->coreMessengerMapping[spl_object_hash($messenger)]);
            $this->pool->detach($messenger);
            $messenger->terminate();
            return;
        }

        $this->readyPool->enqueue($messenger);
    }

    protected function spawnProcess()
    {
        if ($this->detectingCores) {
            return;
        }

        try {
            $this->spawnProcessAtAddress($this->getFreeAddress());
        } catch (\Exception $exception) {
            echo $exception->getMessage(), PHP_EOL;
        }
    }
}
