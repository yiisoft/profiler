<?php

declare(strict_types=1);

namespace Yiisoft\Profiler;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use function get_class;

/**
 * Profiler provides profiling support. It stores profiling messages in the memory and sends them to different targets
 * according to {@see Profiler::$targets}.
 *
 * For more details and usage information on Profiler, see the [guide article on profiling](guide:runtime-profiling)
 */
final class Profiler implements ProfilerInterface
{
    /**
     * @var bool whether to profiler is enabled. Defaults to true.
     * You may use this field to disable writing of the profiling messages and thus save the memory usage.
     */
    private bool $enabled = true;

    /**
     * @var Message[] complete profiling messages.
     * Each message has a following keys:
     *
     * - message: string, profiling token.
     * - category: string, message category.
     * - nestedLevel: int, profiling message nested level.
     * - beginTime: float, profiling begin timestamp obtained by microtime(true).
     * - endTime: float, profiling end timestamp obtained by microtime(true).
     * - duration: float, profiling block duration in milliseconds.
     * - beginMemory: int, memory usage at the beginning of profile block in bytes, obtained by `memory_get_usage()`.
     * - endMemory: int, memory usage at the end of profile block in bytes, obtained by `memory_get_usage()`.
     * - memoryDiff: int, a diff between 'endMemory' and 'beginMemory'.
     */
    private array $messages = [];

    /**
     * @var LoggerInterface logger to be used for message export.
     */
    private LoggerInterface $logger;

    /**
     * @var array pending profiling messages, e.g. the ones which have begun but not ended yet.
     */
    private array $pendingMessages = [];

    /**
     * @var int current profiling messages nested level.
     */
    private int $nestedLevel = 0;

    /**
     * @var array|Target[] the profiling targets. Each array element represents a single {@see Target} instance.
     */
    private array $targets = [];

    /**
     * Initializes the profiler by registering {@see flush()} as a shutdown function.
     *
     * @param LoggerInterface $logger
     * @param array $targets
     */
    public function __construct(LoggerInterface $logger, array $targets = [])
    {
        $this->logger = $logger;
        $this->setTargets($targets);
        register_shutdown_function([$this, 'flush']);
    }

    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * @return bool the profile enabled.
     *
     * {@see enabled}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return Message[] the messages profiler.
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return Target[] the profiling targets. Each array element represents a single {@see Target|profiling target}
     * instance.
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    /**
     * @param Target[] $targets the profiling targets. Each array element represents a single {@see Target} instance.
     */
    private function setTargets(array $targets): void
    {
        foreach ($targets as $name => $target) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (!$target instanceof Target) {
                throw new \InvalidArgumentException(
                    'Target should be an instance of \Yiisoft\Profiler\Target, "' . get_class($target) . '" given.'
                );
            }
        }
        $this->targets = $targets;
    }

    public function begin(string $token, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $category = $context['category'] ?? 'application';
        $context = array_merge(
            $context,
            [
                'token' => $token,
                'category' => $category,
                'nestedLevel' => $this->nestedLevel,
                'time' => microtime(true),
                'beginTime' => microtime(true),
                'beginMemory' => memory_get_usage(),
            ]
        );

        $message = new Message($category, $token, $context);

        $this->pendingMessages[$category][$token][] = $message;
        $this->nestedLevel++;
    }

    public function end(string $token, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $category = $context['category'] ?? 'application';

        if (empty($this->pendingMessages[$category][$token])) {
            throw new \RuntimeException(
                sprintf(
                    'Unexpected %s::end() call for category "%s" token "%s". A matching begin() was not found.',
                    self::class,
                    $category,
                    $token
                )
            );
        }

        /** @var Message $message */
        $message = array_pop($this->pendingMessages[$category][$token]);
        if (empty($this->pendingMessages[$category][$token])) {
            unset($this->pendingMessages[$category][$token]);

            if (empty($this->pendingMessages[$category])) {
                unset($this->pendingMessages[$category]);
            }
        }

        $context = array_merge(
            $message->context(),
            $context,
            [
                'endTime' => microtime(true),
                'endMemory' => memory_get_usage(),
            ]
        );

        $context['duration'] = $context['endTime'] - $context['beginTime'];
        $context['memoryDiff'] = $context['endMemory'] - $context['beginMemory'];

        $this->messages[] = new Message($category, $message->message(), $context);
        $this->nestedLevel--;
    }

    public function flush(): void
    {
        foreach ($this->pendingMessages as $category => $categoryMessages) {
            $this->logCategoryMessages($category, $categoryMessages);
        }

        $this->pendingMessages = [];
        $this->nestedLevel = 0;

        if (empty($this->messages)) {
            return;
        }

        $messages = $this->messages;

        // new messages could appear while the existing ones are being handled by targets
        $this->messages = [];

        $this->dispatch($messages);
    }

    /**
     * Dispatches the profiling messages to {@see targets}.
     *
     * @param array $messages the profiling messages.
     */
    private function dispatch(array $messages): void
    {
        foreach ($this->targets as $target) {
            $target->collect($messages);
        }
    }

    /**
     * @param string $category
     * @param array $categoryMessages
     */
    private function logCategoryMessages(string $category, array $categoryMessages): void
    {
        foreach ($categoryMessages as $token => $messages) {
            if (!empty($messages)) {
                $this->logger->log(
                    LogLevel::WARNING,
                    sprintf(
                        'Unclosed profiling entry detected: category "%s" token "%s" %s',
                        $category,
                        $token,
                        __METHOD__
                    )
                );
            }
        }
    }
}
