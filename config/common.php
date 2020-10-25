<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\Profiler\Profiler;

return [
    Profiler::class => [
        '__class' => Profiler::class,
        '__construct()' => [
            Reference::to(LoggerInterface::class)
        ]
    ]
];
