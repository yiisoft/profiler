<?php

declare(strict_types=1);

use Psr\Log\LogLevel;
use Yiisoft\Profiler\Target\FileTarget;
use Yiisoft\Profiler\Target\LogTarget;

return [
    'yiisoft/profiler' => [
        'targets' => [
            'log' => LogTarget::class,
            'file' => FileTarget::class,
        ],
        'targets.params' => [
            'log' => [
                'include' => [],
                'exclude' => [],
                'enabled' => true,
                'level' => LogLevel::DEBUG,
            ],
            'file' => [
                'include' => [],
                'exclude' => [],
                'enabled' => true,
                'filename' => '@runtime/profiling/{date}-{time}.txt',
                'dirMode' => 0775,
            ],
        ],
    ],
];
