<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Profiler\Message;
use Yiisoft\Profiler\Target\AbstractTarget;

/**
 * @group profile
 */
final class AbstractTargetTest extends TestCase
{
    /**
     * Data provider for {@see testFilterMessages()}
     *
     * @return array test data
     */
    public static function dataProviderFilterMessages(): array
    {
        return [
            [
                [new Message('foo', 'test', ['category' => 'foo'])],
                [],
                [],
                [new Message('foo', 'test', ['category' => 'foo'])],
            ],
            [
                [new Message('foo', 'test', ['category' => 'foo'])],
                ['foo'],
                [],
                [new Message('foo', 'test', ['category' => 'foo'])],
            ],
            [
                [new Message('foo', 'test', ['category' => 'foo'])],
                ['some'],
                [],
                [],
            ],
            [
                [new Message('foo', 'test', ['category' => 'foo'])],
                [],
                ['foo'],
                [],
            ],
            [
                [new Message('foo', 'test', ['category' => 'foo'])],
                [],
                ['some'],
                [new Message('foo', 'test', ['category' => 'foo'])],
            ],
        ];
    }

    /**
     * @covers \Yiisoft\Profiler\Target\AbstractTarget::filterMessages()
     * @covers \Yiisoft\Profiler\Target\AbstractTarget::isCategoryMatched()
     * @covers \Yiisoft\Profiler\Target\AbstractTarget::include()
     * @covers \Yiisoft\Profiler\Target\AbstractTarget::exclude()
     */
    #[DataProvider('dataProviderFilterMessages')]
    public function testFilterMessages(array $messages, array $categories, array $except, array $expected): void
    {
        /* @var $target AbstractTarget|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this
            ->getMockBuilder(AbstractTarget::class)
            ->getMockForAbstractClass();

        $target = $target
            ->include($categories)
            ->exclude($except);

        $this->assertEquals($expected, $this->invokeMethod($target, 'filterMessages', [$messages]));
    }

    public function testInclude(): void
    {
        /* @var $target AbstractTarget|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this
            ->getMockBuilder(AbstractTarget::class)
            ->onlyMethods(['include'])
            ->getMockForAbstractClass();

        $target
            ->expects($this->once())
            ->method('include')
            ->willReturnSelf();

        $this->assertEquals($target, $target->include(['test']));
    }

    public function testExclude(): void
    {
        /* @var $target AbstractTarget|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this
            ->getMockBuilder(AbstractTarget::class)
            ->onlyMethods(['exclude'])
            ->getMockForAbstractClass();

        $target
            ->expects($this->once())
            ->method('exclude')
            ->willReturnSelf();

        $this->assertEquals($target, $target->exclude(['test']));
    }

    public function testEnable(): void
    {
        /* @var $target AbstractTarget|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this
            ->getMockBuilder(AbstractTarget::class)
            ->onlyMethods(['enable'])
            ->getMockForAbstractClass();

        $target
            ->expects($this->once())
            ->method('enable');

        $target->enable();
    }

    public function testEnabled(): void
    {
        /* @var $target AbstractTarget|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this
            ->getMockBuilder(AbstractTarget::class)
            ->onlyMethods(['export'])
            ->getMock();

        $target
            ->expects($this->once())
            ->method('export');

        $target->enable();

        $target->collect([new Message('foo', 'test', ['category' => 'foo'])]);

        $this->assertTrue($target->isEnabled());
    }

    public function testDisabled(): void
    {
        /* @var $target AbstractTarget|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this
            ->getMockBuilder(AbstractTarget::class)
            ->onlyMethods(['export'])
            ->getMock();

        $target
            ->expects($this->exactly(0))
            ->method('export');

        $target->enable(false);

        $target->collect([new Message('foo', 'test', ['category' => 'foo'])]);

        $this->assertFalse($target->isEnabled());
    }
}
