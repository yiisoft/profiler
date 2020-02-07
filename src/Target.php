<?php

declare(strict_types=1);

namespace Yiisoft\Profiler;

/**
 * Target is the base class for all profiling target classes.
 *
 * A profile target object will filter the messages stored by {@see Profiler} according
 * to its {@see categories} and {@see except} properties.
 *
 * For more details and usage information on Target,
 * see the [guide article on profiling & targets](guide:runtime-profiling).
 */
abstract class Target
{
    /**
     * @var bool whether to enable this log target. Defaults to true.
     */
    public bool $enabled = true;

    /**
     * @var array list of message categories that this target is interested in. Defaults to empty, meaning all
     * categories.
     *
     * You can use an asterisk at the end of a category so that the category may be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\*' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     */
    public array $categories = [];

    /**
     * @var array list of message categories that this target is NOT interested in. Defaults to empty, meaning no
     * uninteresting messages.
     *
     * If this property is not empty, then any category listed here will be excluded from {@see categories}.
     * You can use an asterisk at the end of a category so that the category can be used to
     * match those categories sharing the same common prefix. For example, 'Yiisoft\Db\*' will match
     * categories starting with 'Yiisoft\Db\', such as `Yiisoft\Db\Connection`.
     *
     * {@see categories}
     */
    public array $except = [];

    /**
     * Processes the given log messages.
     *
     * This method will filter the given messages with {@see levels} and {@see categories}.
     * And if requested, it will also export the filtering result to specific medium (e.g. email).
     * @param array $messages profiling messages to be processed. See {@see Profiler::$messages} for the structure
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
     * Exports profiling messages to a specific destination.
     *
     * Child classes must implement this method.
     * @param array $messages profiling messages to be exported.
     */
    abstract public function export(array $messages);

    /**
     * Filters the given messages according to their categories.
     *
     * @param array $messages messages to be filtered.
     * The message structure follows that in {@see Profiler::$messages}.
     * @return array the filtered messages.
     */
    protected function filterMessages(array $messages): array
    {
        foreach ($messages as $i => $message) {
            $matched = empty($this->categories);

            foreach ($this->categories as $category) {
                if ($message['category'] === $category || (!empty($category)
                    && substr_compare($category, '*', -1, 1) === 0
                    && strpos($message['category'], rtrim($category, '*')) === 0)) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($this->except as $category) {
                    $prefix = rtrim($category, '*');
                    if (($message['category'] === $category || $prefix !== $category)
                        && strpos($message['category'], $prefix) === 0) {
                        $matched = false;
                        break;
                    }
                }
            }

            if (!$matched) {
                unset($messages[$i]);
            }
        }
        return $messages;
    }
}
