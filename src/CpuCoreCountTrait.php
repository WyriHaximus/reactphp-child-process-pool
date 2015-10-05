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
        $addresses = [];
        for ($i = 0; $i < $coreCount; $i++) {
            $addresses[] = $i;
        }

        return \React\Promise\resolve($addresses);
    }

    protected function spawnProcessAtAddress($address)
    {
        $processOptions = isset($this->options['processOptions']) ? $this->options['processOptions'] : [];
        $process = $this->rebuildProcess($address);
        Factory::parent($process, $this->loop, $processOptions)->then(function (Messenger $messenger) {
            $this->pool->attach($messenger);
            $this->readyPool->enqueue($messenger);
        }, function ($error) {
            $this->emit('error', [$error, $this]);
        });
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
