<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Profiler\Target\FileTarget;
use Yiisoft\Profiler\Target\LogTarget;

final class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $profiler = $container->get(ProfilerInterface::class);
        $logTarget = $container->get(LogTarget::class);
        $fileTarget = $container->get(FileTarget::class);

        $this->assertInstanceOf(Profiler::class, $profiler);
        $this->assertInstanceOf(LogTarget::class, $logTarget);
        $this->assertInstanceOf(FileTarget::class, $fileTarget);
    }

    private function createContainer(?array $params = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($params)
                +
                [
                    LoggerInterface::class => NullLogger::class,
                    Aliases::class => [
                        '__construct()' => [
                            [
                                'runtime' => __DIR__ . '/environment/runtime',
                            ],
                        ],
                    ],
                ]
            )
        );
    }

    private function getDiConfig(?array $params = null): array
    {
        if ($params === null) {
            $params = $this->getParams();
        }
        return require dirname(__DIR__) . '/config/di.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
