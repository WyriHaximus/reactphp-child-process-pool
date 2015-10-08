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

trait CpuCoreCountTrait
{
    protected $coreMessengerMapping = [];
    protected $availableAddresses = [];

    protected function setUpStrategies()
    {
        if (
            !isset($this->options['strategies']['core']) ||
            !($this->options['strategies']['core'] instanceof CountInterface)
        ) {
            $this->options['strategies']['core'] = new Nproc($this->loop);
        }

        if (
            !isset($this->options['strategies']['affinity']) ||
            !($this->options['strategies']['affinity'] instanceof AffinityInterface)
        ) {
            $this->options['strategies']['affinity'] = new Taskset();
        }
    }

    protected function detectCoreCount()
    {
        return $this->options['strategies']['core']->execute()->then(function ($count) {
            return \React\Promise\resolve($count);
        });
    }

    protected function resolveCoreAddresses($coreCount)
    {
        for ($i = 0; $i < $coreCount; $i++) {
            $this->availableAddresses[$i] = $i;
        }

        return \React\Promise\resolve($this->availableAddresses);
    }

    protected function spawnProcessAtAddress($address)
    {
        $this->startingProcesses++;
        unset($this->availableAddresses[$address]);
        $processOptions = isset($this->options['processOptions']) ? $this->options['processOptions'] : [];
        $process = $this->rebuildProcess($address);
        Factory::parent($process, $this->loop, $processOptions)->then(function (Messenger $messenger) use ($address) {
            $this->startingProcesses--;
            $this->pool->attach($messenger);
            $this->readyPool->enqueue($messenger);
            $this->coreMessengerMapping[spl_object_hash($messenger)] = $address;
            $messenger->on('exit', function () use ($messenger, $address) {
                $this->pool->detach($messenger);
                $this->availableAddresses[$address] = $address;
                unset($this->coreMessengerMapping[spl_object_hash($messenger)]);
                $messengers = [];
                while ($this->readyPool->count() > 0) {
                    $queuedMessenger = $this->readyPool->dequeue();
                    if ($queuedMessenger === $messenger) {
                        continue;
                    }
                    $messengers[] = $queuedMessenger;
                }

                array_walk($messengers, function (Messenger $messenger) {
                    $this->readyPool->enqueue($messenger);
                });

                if (
                    $this->callQueue->count() > 0 &&
                    $this->readyPool->count() <= $this->options['min_size'] &&
                    (
                        $this->startingProcesses + $this->pool->count()
                    ) < $this->options['max_size']
                ) {
                    $this->spawnProcess();
                }
            });
        }, function ($error) {
            $this->startingProcesses--;
            $this->emit('error', [$error, $this]);
        });
    }

    protected function getFreeAddress()
    {
        foreach ($this->availableAddresses as $address) {
            unset($this->availableAddresses[$address]);
            return $address;
        }

        throw new \Exception('All cores in use!');
    }

    protected function rebuildProcess($address)
    {
        return new Process(
            $this->options['strategies']['affinity']->execute(
                $address,
                $this->getProcessPropertyValue('cmd')
            ),
            $this->getProcessPropertyValue('cwd'),
            $this->getProcessPropertyValue('env'),
            []
        );
    }

    protected function getProcessPropertyValue($property)
    {
        $reflectionProperty = (new \ReflectionClass($this->sourceProcess))->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($this->sourceProcess);
    }
}
