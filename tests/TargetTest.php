<?php
declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Profiler\Target;

/**
 * @group profile
 */
final class TargetTest extends TestCase
{
    /**
     * Data provider for {@see testFilterMessages()}
     * @return array test data
     */
    public function dataProviderFilterMessages()
    {
        return [
            [
                [['category' => 'foo']],
                [],
                [],
                [['category' => 'foo']],
            ],
            [
                [['category' => 'foo']],
                ['foo'],
                [],
                [['category' => 'foo']],
            ],
            [
                [['category' => 'foo']],
                ['some'],
                [],
                [],
            ],
            [
                [['category' => 'foo']],
                [],
                ['foo'],
                [],
            ],
            [
                [['category' => 'foo']],
                [],
                ['some'],
                [['category' => 'foo']],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFilterMessages
     *
     * @covers \Yiisoft\Profile\Target::filterMessages()
     *
     * @param array $messages
     * @param array $categories
     * @param array $except
     * @param array $expected
     */
    public function testFilterMessages(array $messages, array $categories, array $except, array $expected)
    {
        /* @var $target Target|\PHPUnit_Framework_MockObject_MockObject */
        $target = $this->getMockBuilder(Target::class)->getMockForAbstractClass();

        $target->categories = $categories;
        $target->except = $except;

        $this->assertEquals($expected, $this->invokeMethod($target, 'filterMessages', [$messages]));
    }

    /**
     * @depends testFilterMessages
     */
    public function testEnabled()
    {
        /* @var $target Target|\PHPUnit_Framework_MockObject_MockObject */
        $target = $this->getMockBuilder(Target::class)
            ->setMethods(['export'])
            ->getMock();

        $target->expects($this->exactly(0))->method('export');

        $target->enabled = false;

        $target->collect([['category' => 'foo']]);

        $target = $this->getMockBuilder(Target::class)
            ->setMethods(['export'])
            ->getMock();

        $target->expects($this->exactly(1))->method('export');

        $target->enabled = true;

        $target->collect([['category' => 'foo']]);
    }
}
