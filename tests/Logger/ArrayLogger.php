<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests\Logger;

use Psr\Log\AbstractLogger;

class ArrayLogger extends AbstractLogger
{
    private array $messages = [];

    public function log($level, $message, array $context = []): void
    {
        $this->messages[$level][$message] = $context;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
