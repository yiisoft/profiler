<?php
namespace Yiisoft\Profiler\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Profiler\LogTarget;
use Yiisoft\Profiler\Tests\Logger\ArrayLogger;

class LogTargetTest extends TestCase
{
    public function testExport()
    {
        $logLevel = 'test';
        $token = 'test-token';
        $arrayLogger = new ArrayLogger();
        $target = new LogTarget($arrayLogger, $logLevel);
        $message = [
            'category' => $logLevel,
            'token' => $token,
            'beginTime' => 123,
            'endTime' => 321,
            'time' => 123,
        ];
        $target->export([$message]);
        $logMessages = $arrayLogger->getMessages();
        $this->assertEquals($logMessages[$logLevel][$token], $message);
    }
}
