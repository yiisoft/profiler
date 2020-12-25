<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Target;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yiisoft\Profiler\Message;

/**
 * LogTarget saves profiling messages as a log messages.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'yiisoft/profiler' => [
 *         'targets' => [
 *             Yiisoft\Profiler\LogTarget::class,
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 */
final class LogTarget extends AbstractTarget
{
    /**
     * @var LoggerInterface logger to be used for message export.
     */
    private LoggerInterface $logger;

    /**
     * @var ?string log level to be used for messages export.
     */
    private ?string $logLevel;

    public function __construct(LoggerInterface $logger, ?string $logLevel = LogLevel::DEBUG)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * @param Message[] $messages
     */
    public function export(array $messages): void
    {
        foreach ($messages as $message) {
            $this->logger->log($this->logLevel, $message->message(), $message->context());
        }
    }
}
