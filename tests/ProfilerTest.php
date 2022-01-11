<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use InvalidArgumentException;
use Psr\Log\NullLogger;
use RuntimeException;
use stdClass;
use Yiisoft\Profiler\Target\LogTarget;
use Yiisoft\Profiler\Message;
use Yiisoft\Profiler\Profiler;

final class ProfilerTest extends TestCase
{
    /**
     * @covers \Yiisoft\Profiler\Profiler::setTargets()
     * @covers \Yiisoft\Profiler\Profiler::getTargets()
     */
    public function testSetupTarget(): void
    {
        $target = new LogTarget(new NullLogger());
        $profiler = new Profiler($this->logger, [$target]);

        $this->assertEquals([$target], $profiler->getTargets());
        $this->assertSame($target, $profiler->getTargets()[0]);
    }

    public function testEnabled(): void
    {
        $profiler = new Profiler($this->logger);

        $profiler = $profiler->enable(false);

        $profiler->begin('test');
        $profiler->end('test');

        $this->assertEmpty($profiler->getMessages());

        $profiler = $profiler->enable();

        $this->assertTrue($profiler->isEnabled());

        $profiler->begin('test');
        $profiler->end('test');

        $this->assertCount(1, $profiler->getMessages());
    }

    /**
     * @covers \Yiisoft\Profiler\Profiler::flush()
     * @covers \Yiisoft\Profiler\Profiler::dispatch()
     * @covers \Yiisoft\Profiler\Profiler::logCategoryMessages()
     */
    public function testFlushWithDispatch(): void
    {
        $profiler = new Profiler($this->logger);


        $profiler->begin('anything', ['category' => 'test']);
        $profiler->end('anything', ['category' => 'test']);

        $profiler->flush();

        $this->assertEmpty($profiler->getMessages());
    }

    public function testFlushWithEmptyMessages(): void
    {
        $profiler = new Profiler($this->logger);

        $profiler->flush();

        $this->assertEmpty($profiler->getMessages());
    }

    public function testBeginWithoutEnd(): void
    {
        $profiler = new Profiler($this->logger);

        $profiler->begin('test');

        $profiler->flush();

        $this->assertEmpty($profiler->getMessages());
    }

    public function testNestedMessages(): void
    {
        $profiler = new Profiler($this->logger);

        $profiler->begin('test');
        $profiler->begin('test');
        $profiler->end('test');
        $profiler->end('test');

        $this->assertCount(2, $profiler->getMessages());
        $this->assertContainsOnlyInstancesOf(Message::class, $profiler->getMessages());
    }

    public function testFindMessages(): void
    {
        $profiler = new Profiler($this->logger);

        $profiler->begin('test');
        $profiler->end('test');
        $profiler->begin('test');
        $profiler->end('test');
        $profiler->begin('another test');
        $profiler->end('another test');

        $this->assertCount(2, $profiler->findMessages('test'));
        $this->assertContainsOnlyInstancesOf(Message::class, $profiler->findMessages('test'));
    }

    /**
     * @depends testNestedMessages
     */
    public function testNestedLevel(): void
    {
        $profiler = new Profiler($this->logger);

        $profiler->begin('outer');
        $profiler->begin('inner');
        $profiler->end('inner');
        $profiler->end('outer');
        $profiler->begin('not-nested');
        $profiler->end('not-nested');

        $outerMessage = null;
        $innerMessage = null;
        $notNestedMessage = null;

        foreach ($profiler->getMessages() as $message) {
            if ($message->token() === 'outer') {
                $outerMessage = $message;
                continue;
            }
            if ($message->token() === 'inner') {
                $innerMessage = $message;
                continue;
            }
            if ($message->token() === 'not-nested') {
                $notNestedMessage = $message;
                continue;
            }
        }

        $this->assertSame(0, $outerMessage->context('nestedLevel'));
        $this->assertSame(1, $innerMessage->context('nestedLevel'));
        $this->assertSame(0, $notNestedMessage->context('nestedLevel'));
    }

    public function testProfileEndWithoutBegin(): void
    {
        $profiler = new Profiler($this->logger);

        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage(
            'Unexpected ' . Profiler::class . '::end() call for category "application" token "test". A matching begin() was not found.'
        );
        $profiler->end('test');
    }

    public function testWrongTarget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Target "0" should be an instance of Yiisoft\Profiler\Target\TargetInterface, "' . stdClass::class . '" given.'
        );
        new Profiler($this->logger, [new stdClass()]);
    }

    public function testWrongTargetWithStringValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Target "0" should be an instance of Yiisoft\Profiler\Target\TargetInterface, "string" given.'
        );
        new Profiler($this->logger, [stdClass::class]);
    }
}
