<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Target;

/**
 * Target interface defines a profiling target.
 *
 * The target receives profiling messages and is sending these
 * to a certain medium or system. It may, as well, filter out
 * messages it does not need.
 */
interface TargetInterface
{
    /**
     * Processes the given log messages.
     *
     * @param array $messages Profiling messages to be processed. See {@see Profiler::$messages} for the structure
     * of each message.
     */
    public function collect(array $messages): void;

    /**
     * Enable or disable target.
     *
     * @param bool $value
     */
    public function enable(bool $value = true): void;
}
