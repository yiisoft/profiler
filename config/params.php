<?php

declare(strict_types=1);

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
                //'level' => LogLevel::DEBUG,
            ],
            'file' => [
                'include' => [],
                'exclude' => [],
                //'filename' => null,
                //'dirMode' => null,
            ],
        ],
    ],
];
