<?php

declare(strict_types=1);

namespace Yiisoft\Profiler;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Profiler provides profiling support. It stores profiling messages in the memory and sends them to different targets
 * according to {@see targets}.
 *
 * For more details and usage information on Profiler, see the [guide article on profiling](guide:runtime-profiling)
 */
class Profiler implements ProfilerInterface
{
    /**
     * @var bool whether to profiler is enabled. Defaults to true.
     * You may use this field to disable writing of the profiling messages and thus save the memory usage.
     */
    private bool $enabled = true;

    /**
     * @var array[] complete profiling messages.
     * Each message has a following keys:
     *
     * - token: string, profiling token.
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
     * @var array|Target[] the profiling targets. Each array element represents a single {@see Target|profiling target}
     * instance or the configuration for creating the profiling target instance.
     */
    private array $targets = [];

    /**
     * @var bool whether {@see targets} have been initialized, e.g. ensured to be objects.
     */
    private bool $isTargetsInitialized = false;


    /**
     * Initializes the profiler by registering {@see flush()} as a shutdown function.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        register_shutdown_function([$this, 'flush']);
    }

    /**
     * @return bool the profile enabled.
     *
     * {@see enabled}
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array the messages profiler.
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
        if (!$this->isTargetsInitialized) {
            foreach ($this->targets as $name => $target) {
                if (!$target instanceof Target) {
                    $this->targets[$name] = new $target['__class']($target['logger'], $target['level']);
                }
            }
            $this->isTargetsInitialized = true;
        }

        return $this->targets;
    }

    /**
     * Set the profiler enabled or disabled.
     *
     * @param bool $value
     *
     * @return void
     *
     * {@see enabled}
     */
    public function setEnabled(bool $value): void
    {
        $this->enabled = $value;
    }

    /**
     * Set messages profiler.
     *
     * @param array $value
     *
     * @return void
     *
     * {@see messages}
     */
    public function setMessages(array $value): void
    {
        $this->messages = $value;

        $this->dispatch($this->messages);
    }

    /**
     * @param array|Target[] $targets the profiling targets. Each array element represents a single
     * {@see Target|profiling target} instance or the configuration for creating the profiling target instance.
     */
    public function setTargets(array $targets): void
    {
        $this->targets = $targets;
        $this->isTargetsInitialized = false;
    }

    /**
     * Adds extra target to {@see targets}.
     *
     * @param Target|array $target the log target instance or its DI compatible configuration.
     * @param string|null $name array key to be used to store target, if `null` is given target will be append
     * to the end of the array by natural integer key.
     */
    public function addTarget($target, ?string $name = null): void
    {
        if (!$target instanceof Target) {
            $this->isTargetsInitialized = false;
        }

        if ($name === null) {
            $this->targets[] = $target;
        } else {
            $this->targets[$name] = $target;
        }
    }

    public function begin(string $token, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $category = $context['category'] ?? 'application';

        $message = array_merge($context, [
            'token' => $token,
            'category' => $category,
            'nestedLevel' => $this->nestedLevel,
            'beginTime' => microtime(true),
            'beginMemory' => memory_get_usage(),
        ]);

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
            throw new \InvalidArgumentException(
                'Unexpected ' . static::class .
                '::end() call for category "' .
                $category .
                '" token "' .
                $token . '". A matching begin() is not found.'
            );
        }

        $message = array_pop($this->pendingMessages[$category][$token]);
        if (empty($this->pendingMessages[$category][$token])) {
            unset($this->pendingMessages[$category][$token]);

            if (empty($this->pendingMessages[$category])) {
                unset($this->pendingMessages[$category]);
            }
        }

        $message = array_merge(
            $message,
            $context,
            [
                'endTime' => microtime(true),
                'endMemory' => memory_get_usage(),
            ]
        );

        $message['duration'] = $message['endTime'] - $message['beginTime'];
        $message['memoryDiff'] = $message['endMemory'] - $message['beginMemory'];

        $this->messages[] = $message;
        $this->nestedLevel--;
    }

    public function flush(): void
    {
        foreach ($this->pendingMessages as $category => $categoryMessages) {
            foreach ($categoryMessages as $token => $messages) {
                if (!empty($messages)) {
                    $this->logger->log(
                        LogLevel::WARNING,
                        'Unclosed profiling entry detected: category "' . $category . '" token "' . $token . '"' . ' ' .
                        __METHOD__
                    );
                }
            }
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
    protected function dispatch(array $messages): void
    {
        foreach ($this->getTargets() as $target) {
            $target->collect($messages);
        }
    }
}
