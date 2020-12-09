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
    use MessageFilterTrait;
    /**
     * @var bool whether to enable this log target. Defaults to true.
     */
    public bool $enabled = true;

    /**
     * Processes the given log messages.
     *
     * This method will filter the given messages with {@see levels} and {@see categories}.
     * And if requested, it will also export the filtering result to specific medium (e.g. email).
     *
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
     *
     * @param Message[] $messages profiling messages to be exported.
     */
    abstract public function export(array $messages);
}
