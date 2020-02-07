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
}
