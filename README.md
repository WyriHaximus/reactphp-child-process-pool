# react-child-process-pool

[![Build Status](https://travis-ci.org/WyriHaximus/reactphp-child-process-pool.png)](https://travis-ci.org/WyriHaximus/reactphp-child-process-pool)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/react-child-process-pool/v/stable.png)](https://packagist.org/packages/WyriHaximus/react-child-process-pool)
[![Total Downloads](https://poser.pugx.org/wyrihaximus/react-child-process-pool/downloads.png)](https://packagist.org/packages/wyrihaximus/react-child-process-pool)
[![Code Coverage](https://scrutinizer-ci.com/g/WyriHaximus/react-child-process-pool/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WyriHaximus/react-child-process-pool/?branch=master)
[![License](https://poser.pugx.org/wyrihaximus/react-child-process-pool/license.png)](https://packagist.org/packages/wyrihaximus/react-child-process-pool)
[![PHP 7 ready](http://php7ready.timesplinter.ch/WyriHaximus/reactphp-child-process-pool/badge.svg)](https://travis-ci.org/WyriHaximus/reactphp-child-process-pool)

## Installation ##

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require reactphp-child-process-pool
```

## Pools ##

* `DummyPool` - Meant for testing, doesn't do anything but complies to it's contract
* `Fixed` - Spawns a given fixed amount of workers
* `Flexible` - Spawns workers as a needed basis, given a minimum and maximum it will spawn within those values
* `CpuCoreCountFixed` - Spawns the same amount of workers as you have CPU cores and affinitiates them all to a different code
* `CpuCoreCountFlexible` - Same as `Flexible` and `CpuCoreCountFixed` where the maximum amount of workers is the CPU core count

## Usage ##

This package pools [`wyrihaximus/reactphp-child-process-messenger`](https://github.com/WyriHaximus/reactphp-child-process-messenger), please see that package for details how to use it.

## License ##

Copyright 2015 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

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


### Gabi was here