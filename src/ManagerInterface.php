<?php

namespace WyriHaximus\React\ChildProcess\Pool;

use Evenement\EventEmitterInterface;
use React\Promise\PromiseInterface;


interface ManagerInterface extends EventEmitterInterface
{
    /**
     * @param ProcessCollectionInterface $processCollection
     * @param array $options
     */
    public function __construct(ProcessCollectionInterface $processCollection, array $options = []);

    /**
     * @return PromiseInterface
     */
    public function getAvailableProcess();

    /**
     * @return MessengerCollectionInterface
     */
    public function getAllProcesses();

    public function ping();
}
