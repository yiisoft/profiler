<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii Profiler</h1>
    <br>
</p>

The package provides an ability to record performance profiles.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/profiler/v/stable.png)](https://packagist.org/packages/yiisoft/profiler)
[![Total Downloads](https://poser.pugx.org/yiisoft/profiler/downloads.png)](https://packagist.org/packages/yiisoft/profiler)
[![Build Status](https://travis-ci.com/yiisoft/profiler.svg?branch=master)](https://travis-ci.com/yiisoft/profiler)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/profiler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/profiler/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/profiler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/profiler/?branch=master)

## Installation

The package could be installed via composer:

```
composer require --dev yiisoft/profiler
```

## General usage

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Log\NullLogger;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\LogTarget;

$logger = new NullLogger();

$profiler = new Profiler($logger);

$profiler->addTarget(new LogTarget($logger));

$profiler->begin('test');
//...some code
$profiler->end('test');

print_r($profiler->getMessages());
```

### Output

```
Array
(
    [0] => Array
        (
            [token] => test
            [category] => application
            [nestedLevel] => 0
            [beginTime] => 1582902299.2965
            [beginMemory] => 517640
            [endTime] => 1582902299.2965
            [endMemory] => 518392
            [duration] => 1.215934753418E-5
            [memoryDiff] => 752
        )

)

```

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```php
./vendor/bin/phpunit
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```php
./vendor/bin/psalm
```