<?php

declare(strict_types=1);

namespace Yiisoft\Profiler;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * Message is a data object that stores log message data.
 */
final class Message
{
    /**
     * @var string Log message level.
     *
     * @see LogLevel See constants for valid level names.
     */
    private string $level;

    /**
     * @var string Log message.
     */
    private string $message;

    /**
     * @var array Log message context.
     *
     * Message context has a following keys:
     *
     * - category: string, message category.
     * - memory: int, memory usage in bytes, obtained by `memory_get_usage()`.
     * - time: float, message timestamp obtained by microtime(true).
     * - trace: array, debug backtrace, contains the application code call stacks.
     */
    private array $context;

    /**
     * @param mixed $level Log message level.
     * @param mixed $message Log message.
     * @param array $context Log message context.
     *
     * @throws InvalidArgumentException for invalid log message level.
     *
     * @see LoggerTrait::log()
     * @see LogLevel
     */
    public function __construct(string $level, string $message, array $context = [])
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * Gets a log message level.
     *
     * @return string Log message level.
     */
    public function level(): string
    {
        return $this->level;
    }

    /**
     * Gets a log message.
     *
     * @return string Log message.
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Returns a value of the context parameter for the specified name.
     *
     * If no name is specified, the entire context is returned.
     *
     * @param string|null $name The context parameter name.
     * @param mixed $default If the context parameter does not exist, the `$default` will be returned.
     *
     * @return mixed The context parameter value.
     */
    public function context(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->context;
        }

        return $this->context[$name] ?? $default;
    }
}
