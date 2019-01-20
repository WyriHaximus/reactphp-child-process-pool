# Pool wyrihaximus/react-child-process-messenger processes

[![Linux Build Status](https://travis-ci.org/WyriHaximus/reactphp-child-process-pool.png)](https://travis-ci.org/WyriHaximus/reactphp-child-process-pool)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/react-child-process-pool/v/stable.png)](https://packagist.org/packages/WyriHaximus/react-child-process-pool)
[![Total Downloads](https://poser.pugx.org/wyrihaximus/react-child-process-pool/downloads.png)](https://packagist.org/packages/wyrihaximus/react-child-process-pool)
[![Code Coverage](https://scrutinizer-ci.com/g/WyriHaximus/reactphp-child-process-pool/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WyriHaximus/react-child-process-pool/?branch=master)
[![License](https://poser.pugx.org/wyrihaximus/react-child-process-pool/license.png)](https://packagist.org/packages/wyrihaximus/react-child-process-pool)
[![PHP 7 ready](http://php7ready.timesplinter.ch/WyriHaximus/reactphp-child-process-pool/badge.svg)](https://travis-ci.org/WyriHaximus/reactphp-child-process-pool)

## Installation ##

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require wyrihaximus/react-child-process-pool
```

## Pools ##

* `Dummy` - Meant for testing, doesn't do anything but complies to it's contract
* `Fixed` - Spawns a given fixed amount of workers
* `Flexible` - Spawns workers as a needed basis, given a minimum and maximum it will spawn within those values

## Usage ##

This package pools [`wyrihaximus/react-child-process-messenger`](https://github.com/WyriHaximus/reactphp-child-process-messenger), for basic messaging please see that package for details how to use it.

## Creating a pool ##

This package ships with a set factories, which create different pools. (All the options in the following examples are the default options.)

### Dummy ###

Creates a `Dummy` pool:

```php
$loop = EventLoopFactory::create();

Dummy::createFromClass(ReturnChild::class, $loop)->then(function (PoolInterface $pool) {
  // Now you have a Dummy pool, which does absolutely nothing
});
```

### Fixed ###

Creates a `Fixed` pool:

```php
$loop = EventLoopFactory::create();
$options = [
    Options::SIZE => 5,
];
Fixed::createFromClass(ReturnChild::class, $loop, $options)->then(function (PoolInterface $pool) {
    // You now have a pull with 5 always running child processes 
});
```

### Flexible ###

Creates a `Flexible` pool:

```php
$loop = EventLoopFactory::create();
$options = [
    Options::MIN_SIZE => 0,
    Options::MAX_SIZE => 5,
    Options::TTL      => 0,
];
Flexible::createFromClass(ReturnChild::class, $loop, $options)->then(function (PoolInterface $pool) {
    // You now have a pool that spawns no child processes on start.
    // But when you call rpc a new child process will be started for 
    // as long as the pool has work in the queue. With a maximum of five.
});
```

### CpuCoreCountFixed ###

Creates a `Fixed` pool with size set to the number of CPU cores:

```php
$loop = EventLoopFactory::create();

CpuCoreCountFlexible::createFromClass(ReturnChild::class, $loop)->then(function (PoolInterface $pool) {
    // You now have a Fixed pool with a child process assigned to each CPU core.
});
```

### CpuCoreCountFlexible ###

The following example will creates a flexible pool with max size set to the number of CPU cores. Where the `create` method requires you to give it a `React\ChildProcess\Process`. The `createFromClass` lets you pass a classname of a class implementing [`WyriHaximus\React\ChildProcess\Messenger\ChildInterface`](https://github.com/WyriHaximus/reactphp-child-process-messenger/blob/master/src/ChildInterface.php) that will be used as the worker in the client. Take a look at [`WyriHaximus\React\ChildProcess\Messenger\ReturnChild`](https://github.com/WyriHaximus/reactphp-child-process-messenger/blob/master/src/ReturnChild.php) to see how that works.

```php
$loop = EventLoopFactory::create();

CpuCoreCountFlexible::createFromClass(ReturnChild::class, $loop)->then(function (PoolInterface $pool) {
    // You now have a Fixed pool with a child process assigned to each CPU core,
    // which, just like the Flexible pool, will only run when there is something
    // in the queue.
});
```

## License ##

Copyright 2017 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.


### Contributors beyond the commit log
* Gabi Davila - Helping test if my github token will be secure for pull requests on AppVeyor
