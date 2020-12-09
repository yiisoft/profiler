<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Psr\Log\NullLogger;
use Yiisoft\Profiler\LogTarget;
use Yiisoft\Profiler\Message;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\Target;

class ProfilerTest extends TestCase
{
    /**
     * @covers \Yiisoft\Profiler\Profiler::setTargets()
     * @covers \Yiisoft\Profiler\Profiler::getTargets()
     */
    public function testSetupTarget(): void
    {
        $profiler = new Profiler($this->logger);

        $target = new LogTarget(new NullLogger());

        $profiler->setTargets([$target]);

        $this->assertEquals([$target], $profiler->getTargets());
        $this->assertSame($target, $profiler->getTargets()[0]);

        $profiler->setTargets([new LogTarget(new NullLogger(), 'test')]);

        $target = $profiler->getTargets()[0];

        $this->assertInstanceOf(LogTarget::class, $target);
        $this->assertEquals('test', $target->getLogLevel());
    }

    /**
     * @depends testSetupTarget
     *
     * @covers  \Yiisoft\Profiler\Profiler::addTarget()
     */
    public function testAddTarget(): void
    {
        $profiler = new Profiler($this->logger);

        $target = $this->getMockBuilder(Target::class)->getMockForAbstractClass();
        $profiler->setTargets([$target]);

        $namedTarget = $this->getMockBuilder(Target::class)->getMockForAbstractClass();
        $profiler->addTarget($namedTarget, 'test-target');

        $targets = $profiler->getTargets();

        $this->assertCount(2, $targets);
        $this->assertTrue(isset($targets['test-target']));
        $this->assertSame($namedTarget, $targets['test-target']);

        $namelessTarget = $this->getMockBuilder(Target::class)->getMockForAbstractClass();
        $profiler->addTarget($namelessTarget);
        $targets = $profiler->getTargets();

        $this->assertCount(3, $targets);
        $this->assertSame($namelessTarget, array_pop($targets));
    }

    public function testEnabled(): void
    {
        $profiler = new Profiler($this->logger);

        $profiler->setEnabled(false);

        $profiler->begin('test');
        $profiler->end('test');

        $this->assertEmpty($profiler->getMessages());

        $profiler->setEnabled(true);

        $this->assertTrue($profiler->getEnabled());

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

        $messages = [new Message('test', 'anything')];

        $profiler->setMessages($messages);

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
            if ($message->message() === 'outer') {
                $outerMessage = $message;
                continue;
            }
            if ($message->message() === 'inner') {
                $innerMessage = $message;
                continue;
            }
            if ($message->message() === 'not-nested') {
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

        $this->expectException(\RuntimeException::class);
        $this->expectErrorMessage(
            'Unexpected ' . Profiler::class . '::end() call for category "application" token "test". A matching begin() is not found.'
        );
        $profiler->end('test');
    }

    public function testWrongTarget(): void
    {
        $profiler = new Profiler($this->logger);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Target should be \Yiisoft\Profiler\Target instance. "' . \stdClass::class . '" given.'
        );
        $profiler->setTargets([new \stdClass()]);
    }

    public function testSetMessages(): void
    {
        $profiler = new Profiler($this->logger, [new LogTarget($this->logger)]);
        $message = new Message('test', 'something');

        $profiler->setMessages([$message]);

        $this->assertNotEmpty($profiler->getMessages());
        $this->assertNotEmpty($this->logger->getMessages());
        $this->assertSame([$message], $profiler->getMessages());
    }
}
