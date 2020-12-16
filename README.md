<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Profiler</h1>
    <br>
</p>

The package provides an ability to record performance profiles.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/profiler/v/stable.png)](https://packagist.org/packages/yiisoft/profiler)
[![Total Downloads](https://poser.pugx.org/yiisoft/profiler/downloads.png)](https://packagist.org/packages/yiisoft/profiler)
[![Build status](https://github.com/yiisoft/profiler/workflows/build/badge.svg)](https://github.com/yiisoft/profiler/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/profiler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/profiler/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/profiler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/profiler/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fprofiler%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/profiler/master)
[![static analysis](https://github.com/yiisoft/profiler/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/profiler/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/profiler/coverage.svg)](https://shepherd.dev/github/yiisoft/profiler)


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
$target = new LogTarget($logger);

$profiler = new Profiler($logger, [$target]);

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

## Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```php
./vendor/bin/phpunit
```

## Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```php
./vendor/bin/infection
```

## Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/docs/). To run static analysis:

```php
./vendor/bin/psalm
```
