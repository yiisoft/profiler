<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Profiler\LogTarget;

class LogTargetTest extends TestCase
{
    public function testExport(): void
    {
        $logLevel = 'test';
        $token = 'test-token';

        $target = new LogTarget($this->logger, $logLevel);

        $message = [
            'category' => $logLevel,
            'token' => $token,
            'beginTime' => 123,
            'endTime' => 321,
            'time' => 123,
        ];

        $target->export([$message]);
        $logMessages = $this->logger->getMessages();

        $this->assertEquals($logMessages[$logLevel][$token], $message);
    }

    public function testCollect(): void
    {
        $logLevel = 'test*';
        $category = 'test-message';
        $token = 'test-token';

        $target = new LogTarget($this->logger, $logLevel);

        $message = [
            'category' => $category,
            'token' => $token,
            'beginTime' => 123,
            'endTime' => 321,
            'time' => 123,
        ];

        $target->collect([$message]);
        $logMessages = $this->logger->getMessages();

        $this->assertEquals($logMessages[$logLevel][$token], $message);
    }

    public function testCollectWithExceptCategory(): void
    {
        $logLevel = 'test-level';
        $token = 'test-token';

        $target = new LogTarget($this->logger, $logLevel);
        $target->except = ['test*'];

        $message = [
            'category' => $logLevel,
            'token' => $token,
            'beginTime' => 123,
            'endTime' => 321,
            'time' => 123,
        ];

        $target->collect([$message]);
        $logMessages = $this->logger->getMessages();

        $this->assertEmpty($logMessages);
    }

    public function testGetLogLevel(): void
    {
        $target = new LogTarget($this->logger, 'test');

        $this->assertEquals('test', $target->getLogLevel());
    }
}
