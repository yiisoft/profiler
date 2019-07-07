<?php
namespace Yiisoft\Profiler\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Profiler\LogTarget;

class LogTargetTest extends TestCase
{
    /**
     * @covers \yii\profile\LogTarget::setLogger()
     * @covers \yii\profile\LogTarget::getLogger()
     */
    public function testSetupLogger()
    {
        $logger = new NullLogger();
        $target = new LogTarget($logger);

        $this->assertSame($logger, $target->getLogger());
    }

    /**
     * @depends testSetupLogger
     *
     * @covers \yii\profile\LogTarget::export()
     */
    public function testExport()
    {
        /* @var $logger LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->setMethods([
                'log'
            ])
            ->getMockForAbstractClass();

        $target = new LogTarget($logger);
        $target->logLevel = 'test-level';

        $logger->expects($this->once())
            ->method('log')
            ->with($this->equalTo($target->logLevel), $this->equalTo('test-token'));

        $target->export([
            [
                'category' => 'test',
                'token' => 'test-token',
                'beginTime' => 123,
                'endTime' => 321,
            ],
        ]);
    }
}
