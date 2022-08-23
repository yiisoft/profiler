<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerAwareInterface;
use Yiisoft\Profiler\ProfilerAwareTrait;
use Yiisoft\Profiler\Target\LogTarget;
use Yiisoft\Profiler\Tests\Logger\ArrayLogger;

final class ProfilerAwareTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $target = new LogTarget(new NullLogger());
        $profiler = new Profiler(new ArrayLogger(), [$target]);
        $class = new class () implements ProfilerAwareInterface {
            use ProfilerAwareTrait;

            public function getProfiler(): ?Profiler
            {
                return $this->profiler;
            }
        };

        $this->assertNull($class->getProfiler());

        $class->setProfiler($profiler);

        $this->assertSame($profiler, $class->getProfiler());
    }
}
