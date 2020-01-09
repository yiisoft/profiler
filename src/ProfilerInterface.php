<?php
declare(strict_types=1);

namespace Yiisoft\Profiler;

/**
 * ProfilerInterface describes a profiler instance.
 *
 * For more details and usage information on Profiler, see the [guide article on profiling](guide:runtime-profiling)
 */
interface ProfilerInterface
{
    /**
     * Marks the beginning of a code block for profiling.
     *
     * This has to be matched with a call to {@see end()} with the same category name.
     * The begin- and end- calls must also be properly nested. For example,
     *
     * @param string $token token for the code block
     * @param array $context the context data of this profile block
     *
     * {@see endProfile()}
     */
    public function begin(string $token, array $context = []);

    /**
     * Marks the end of a code block for profiling.
     *
     * This has to be matched with a previous call to {@see begin()} with the same category name.
     * @param string $token token for the code block
     * @param array $context the context data of this profile block
     *
     * {@see begin()}
     */
    public function end(string $token, array $context = []);

    /**
     * Flushes profiling messages from memory to actual storage.
     */
    public function flush();
}
