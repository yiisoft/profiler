<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Profiler\Target\LogTarget;
use Yiisoft\Profiler\Message;

final class LogTargetTest extends TestCase
{
    public function testExport(): void
    {
        $logLevel = 'test';
        $token = 'test-token';

        $target = new LogTarget($this->logger, $logLevel);

        $context = [
            'category' => $logLevel,
            'token' => $token,
            'beginTime' => 123,
            'endTime' => 321,
            'time' => 123,
        ];
        $message = new Message($logLevel, $token, $context);

        $target->export([$message]);
        $logMessages = $this->logger->getMessages();

        $this->assertEquals($logMessages[$logLevel][$token], $message->context());
    }

    public function testCollect(): void
    {
        $logLevel = 'test*';
        $category = 'test-message';
        $token = 'test-token';

        $target = new LogTarget($this->logger, $logLevel);

        $context = [
            'category' => $category,
            'token' => $token,
            'beginTime' => 123,
            'endTime' => 321,
            'time' => 123,
        ];
        $message = new Message($category, $token, $context);

        $target->collect([$message]);
        $logMessages = $this->logger->getMessages();

        $this->assertEquals($logMessages[$logLevel][$token], $message->context());
    }

    public function testCollectWithExceptCategory(): void
    {
        $logLevel = 'test-level';
        $token = 'test-token';

        $target = new LogTarget($this->logger, $logLevel);
        $target = $target->exclude(['test*']);

        $context = [
            'category' => $logLevel,
            'token' => $token,
            'beginTime' => 123,
            'endTime' => 321,
            'time' => 123,
        ];
        $message = new Message($logLevel, $token, $context);

        $target->collect([$message]);
        $logMessages = $this->logger->getMessages();

        $this->assertEmpty($logMessages);
    }
}
