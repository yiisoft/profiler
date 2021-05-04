<?php

declare(strict_types=1);

namespace Yiisoft\Profiler;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Yiisoft\Profiler\Target\TargetInterface;

use function get_class;
use function is_object;

/**
 * Profiler provides profiling support. It stores profiling messages in the memory and sends them to different targets
 * according to {@see Profiler::$targets}.
 */
final class Profiler implements ProfilerInterface
{
    /**
     * @var bool Whether to profiler is enabled. Defaults to true.
     * You may use this field to disable writing of the profiling messages and thus save the memory usage.
     */
    private bool $enabled = true;

    /**
     * @var Message[] Complete profiling messages.
     * @see TargetInterface::collect()
     */
    private array $messages = [];

    /**
     * @var LoggerInterface Logger to be used for message export.
     */
    private LoggerInterface $logger;

    /**
     * @var array Pending profiling messages, e.g. the ones which have begun but not ended yet.
     */
    private array $pendingMessages = [];

    /**
     * @var int Current profiling messages nested level.
     */
    private int $nestedLevel = 0;

    /**
     * @var array|TargetInterface[] Profiling targets. Each array element represents
     * a single {@see TargetInterface} instance.
     */
    private array $targets = [];

    /**
     * Initializes the profiler by registering {@see flush()} as a shutdown function.
     *
     * @param LoggerInterface $logger Logger to use.
     * @param array $targets Profiling targets to use.
     */
    public function __construct(LoggerInterface $logger, array $targets = [])
    {
        $this->logger = $logger;
        $this->setTargets($targets);
        register_shutdown_function([$this, 'flush']);
    }

    /**
     * Enable or disable profiler.
     *
     * @return $this
     */
    public function enable(bool $value = true): self
    {
        $new = clone $this;
        $new->enabled = $value;
        return $new;
    }

    /**
     * @return bool If profiler is enabled.
     *
     * {@see enable}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Returns profiler messages.
     *
     * @return Message[] The profiler messages.
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return TargetInterface[] Profiling targets. Each array element represents
     * a single {@see TargetInterface profiling target} instance.
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    /**
     * @param TargetInterface[] $targets Profiling targets. Each array element represents
     * a single {@see TargetInterface} instance.
     */
    private function setTargets(array $targets): void
    {
        foreach ($targets as $name => $target) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (!($target instanceof TargetInterface)) {
                $type = is_object($target) ? get_class($target) : gettype($target);
                throw new InvalidArgumentException(
                    "Target \"$name\" should be an instance of \Yiisoft\Profiler\Target\TargetInterface, \"$type\" given."
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
            throw new RuntimeException(
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

        $this->messages[] = new Message($category, $message->token(), $context);
        $this->nestedLevel--;
    }

    public function findMessages(string $token): array
    {
        $messages = $this->messages;
        return array_filter($messages, static function (Message $message) use ($token) {
            return $message->token() === $token;
        });
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

        // New messages could appear while the existing ones are being handled by targets.
        $this->messages = [];

        $this->dispatch($messages);
    }

    /**
     * Dispatches the profiling messages to targets.
     *
     * @param array $messages The profiling messages.
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
