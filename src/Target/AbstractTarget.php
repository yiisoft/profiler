<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Target;

use Yiisoft\Profiler\Message;
use Yiisoft\Strings\WildcardPattern;

/**
 * Target is the base class for all profiling target classes.
 *
 * A profile target object will filter the messages stored by {@see Profiler} according
 * to its {@see AbstractTarget::include()} and {@see AbstractTarget::exclude()}.
 *
 * For more details and usage information on Target,
 * see the [guide article on profiling & targets](guide:runtime-profiling).
 */
abstract class AbstractTarget implements TargetInterface
{
    /**
     * @var array List of message categories that this target is interested in. Defaults to empty, meaning all
     * categories.
     *
     * You can use an asterisk at the end of a category so that the category may be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\**' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     *
     * @see WildcardPattern
     */
    private array $include = [];

    /**
     * @var array List of message categories that this target is NOT interested in. Defaults to empty, meaning no
     * uninteresting messages.
     *
     * If this property is not empty, then any category listed here will be excluded from {@see include()}.
     * You can use an asterisk at the end of a category so that the category can be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\**' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     *
     * @see WildcardPattern
     */
    private array $exclude = [];

    /**
     * @var bool Whether to enable this log target. Defaults to true.
     */
    private bool $enabled = true;

    /**
     * Processes the given log messages.
     *
     * This method will filter the given messages with {@see include()} and {@see exclude()}.
     * And if requested, it will also export the filtering result to specific medium (e.g. email).
     *
     * @param array $messages Profiling messages to be processed. See {@see Profiler::$messages} for the structure
     * of each message.
     */
    public function collect(array $messages): void
    {
        if (!$this->enabled) {
            return;
        }

        $messages = $this->filterMessages($messages);

        if (count($messages) > 0) {
            $this->export($messages);
        }
    }

    /**
     * @param array $include List of message categories that this target is interested in. Defaults to empty, meaning all
     * categories.
     *
     * You can use an asterisk at the end of a category so that the category may be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\**' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     *
     * @see WildcardPattern
     *
     * @return $this
     */
    public function include(array $include): self
    {
        $new = clone $this;
        $new->include = $include;
        return $new;
    }

    /**
     * @param array $exclude List of message categories that this target is NOT interested in. Defaults to empty, meaning no
     * uninteresting messages.
     *
     * If this property is not empty, then any category listed here will be excluded from {@see include()}.
     * You can use an asterisk at the end of a category so that the category can be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\**' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     *
     * @return $this
     */
    public function exclude(array $exclude): self
    {
        $new = clone $this;
        $new->exclude = $exclude;
        return $new;
    }

    /**
     * Enable or disable target.
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
     * Returns target is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Exports profiling messages to a specific destination.
     *
     * Child classes must implement this method.
     *
     * @param Message[] $messages Profiling messages to be exported.
     */
    abstract public function export(array $messages): void;

    /**
     * Filters the given messages according to their categories.
     *
     * @param Message[] $messages Messages to be filtered.
     * The message structure follows that in {@see Profiler::$messages}.
     *
     * @return array The filtered messages.
     */
    protected function filterMessages(array $messages): array
    {
        foreach ($messages as $i => $message) {
            if (!$this->isCategoryMatched($message->level())) {
                unset($messages[$i]);
            }
        }
        return $messages;
    }

    private function isCategoryMatched(string $category): bool
    {
        $matched = empty($this->include);

        foreach ($this->include as $pattern) {
            if ((new WildcardPattern($pattern))->match($category)) {
                $matched = true;
                break;
            }
        }

        if ($matched) {
            foreach ($this->exclude as $pattern) {
                if ((new WildcardPattern($pattern))->match($category)) {
                    $matched = false;
                    break;
                }
            }
        }
        return $matched;
    }
}
