<?php
declare(strict_types=1);

namespace Yiisoft\Profiler;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * LogTarget saves profiling messages as a log messages.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     Yiisoft\Profiler\Profiler::class => [
 *         'targets' => [
 *             [
 *                 '__class' => Yiisoft\Profiler\LogTarget::class,
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 */
class LogTarget extends Target
{
    /**
     * @var LoggerInterface logger to be used for message export.
     */
    private LoggerInterface $logger;

    /**
     * @var string log level to be used for messages export.
     */
    private ?string $logLevel = null;

    public function __construct(LoggerInterface $logger, ?string $logLevel = LogLevel::DEBUG)
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    public function export(array $messages): void
    {
        foreach ($messages as $message) {
            $message['time'] = $message['beginTime'];

            $this->logger->log($this->logLevel, $message['token'], $message);
        }
    }

    /**
     * @return ?string logLevel
     *
     * {@see logLevel}
     */
    public function getLogLevel(): ?string
    {
        return $this->logLevel;
    }
}
