<?php
declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Factory\Factory;
use Yiisoft\Profiler\Tests\Logger\ArrayLogger;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Factory $factory;
    protected ArrayLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Factory();
        $this->logger = new ArrayLogger();
    }

    /**
     * Invokes a inaccessible method.
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @param bool $revoke whether to make method inaccessible after execution
     *
     * @return mixed
     */
    protected function invokeMethod($object, $method, $args = [], $revoke = true)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);

        if ($revoke) {
            $method->setAccessible(false);
        }

        return $result;
    }
}
