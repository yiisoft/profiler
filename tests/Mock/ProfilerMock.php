<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests\Mock;

use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\Tests\Logger\ArrayLogger;

class ProfilerMock implements \Yiisoft\Profiler\ProfilerInterface
{
    private ArrayLogger $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function begin(string $token, array $context = []): void
    {
        // TODO: Implement begin() method.
    }

    public function end(string $token, array $context = []): void
    {
        // TODO: Implement end() method.
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        // TODO: Implement flush() method.
    }

    public function __call($name, $arguments)
    {
        $class = new \ReflectionClass(Profiler::class);
        $class = $class->newInstance($this->logger);

        return $class->$name($arguments);
    }
}
