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
     *
     * @param array $messages
     * @param array $categories
     * @param array $except
     * @param array $expected
     */
    public function testFilterMessages(array $messages, array $categories, array $except, array $expected): void
    {
        /* @var $target Target|\PHPUnit_Framework_MockObject_MockObject */
        $target = $this->getMockBuilder(Target::class)->getMockForAbstractClass();

        $target->include = $categories;
        $target->exclude = $except;

        $this->assertEquals($expected, $this->invokeMethod($target, 'filterMessages', [$messages]));
    }

    /**
     * @depends testFilterMessages
     */
    public function testEnabled(): void
    {
        /* @var $target Target|\PHPUnit_Framework_MockObject_MockObject */
        $target = $this->getMockBuilder(Target::class)
            ->setMethods(['export'])
            ->getMock();

        $target->expects($this->exactly(0))->method('export');

        $target->enabled = false;

        $target->collect([new Message('foo', 'test', ['category' => 'foo'])]);

        $target = $this->getMockBuilder(Target::class)
            ->setMethods(['export'])
            ->getMock();

        $target->expects($this->once())->method('export');

        $target->enabled = true;

        $target->collect([new Message('foo', 'test', ['category' => 'foo'])]);
    }
}
