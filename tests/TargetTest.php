<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Profiler\Message;
use Yiisoft\Profiler\Target;

/**
 * @group profile
 */
final class TargetTest extends TestCase
{
    /**
     * Data provider for {@see testFilterMessages()}
     *
     * @return array test data
     */
    public function dataProviderFilterMessages(): array
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
     * @dataProvider dataProviderFilterMessages
     *
     * @covers \Yiisoft\Profiler\Target::filterMessages()
     * @covers \Yiisoft\Profiler\Target::isCategoryMatched()
     * @covers \Yiisoft\Profiler\Target::include()
     * @covers \Yiisoft\Profiler\Target::exclude()
     *
     * @param array $messages
     * @param array $categories
     * @param array $except
     * @param array $expected
     */
    public function testFilterMessages(array $messages, array $categories, array $except, array $expected): void
    {
        /* @var $target Target|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this->getMockBuilder(Target::class)->getMockForAbstractClass();

        $target->include($categories)->exclude($except);

        $this->assertEquals($expected, $this->invokeMethod($target, 'filterMessages', [$messages]));
    }

    public function testInclude(): void
    {
        /* @var $target Target|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this->getMockBuilder(Target::class)->onlyMethods(['include'])->getMockForAbstractClass();

        $target->expects($this->once())->method('include')->willReturnSelf();

        $this->assertEquals($target, $target->include(['test']));
    }

    public function testExclude(): void
    {
        /* @var $target Target|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this->getMockBuilder(Target::class)->onlyMethods(['exclude'])->getMockForAbstractClass();

        $target->expects($this->once())->method('exclude')->willReturnSelf();

        $this->assertEquals($target, $target->exclude(['test']));
    }

    public function testEnable(): void
    {
        /* @var $target Target|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this->getMockBuilder(Target::class)->onlyMethods(['enable'])->getMockForAbstractClass();

        $target->expects($this->once())->method('enable')->willReturnSelf();

        $this->assertEquals($target, $target->enable());
    }

    public function testDisable(): void
    {
        /* @var $target Target|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this->getMockBuilder(Target::class)->onlyMethods(['disable'])->getMockForAbstractClass();

        $target->expects($this->once())->method('disable')->willReturnSelf();

        $this->assertEquals($target, $target->disable());
    }

    public function testEnabled(): void
    {
        /* @var $target Target|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this->getMockBuilder(Target::class)
            ->onlyMethods(['export'])
            ->getMock();

        $target->expects($this->once())->method('export');

        $target->enable();

        $target->collect([new Message('foo', 'test', ['category' => 'foo'])]);

        $this->assertTrue($target->isEnabled());
    }

    public function testDisabled(): void
    {
        /* @var $target Target|\PHPUnit\Framework\MockObject\MockObject */
        $target = $this->getMockBuilder(Target::class)
            ->onlyMethods(['export'])
            ->getMock();

        $target->expects($this->exactly(0))->method('export');

        $target->disable();

        $target->collect([new Message('foo', 'test', ['category' => 'foo'])]);

        $this->assertFalse($target->isEnabled());
    }
}
