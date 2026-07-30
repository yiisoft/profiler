<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Profiler\Tests\Logger\ArrayLogger;
use ReflectionObject;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ArrayLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new ArrayLogger();
    }

    /**
     * Invokes an inaccessible method.
     */
    protected function invokeMethod(object $object, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionObject($object);
        $method = $reflection->getMethod($method);
        return $method->invokeArgs($object, $args);
    }
}
