<?php

declare(strict_types=1);

use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Yii\Console\Event\ApplicationShutdown;

return [
    ApplicationShutdown::class => [
        [ProfilerInterface::class, 'flush'],
    ],
];
