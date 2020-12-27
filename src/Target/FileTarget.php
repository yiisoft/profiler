<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Target;

use Yiisoft\Files\FileHelper;
use Yiisoft\Profiler\Message;

/**
 * FileTarget records profiling messages in a file specified via {@see filename}.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'yiisoft/profiler' => [
 *         'targets' => [
 *             'file' => Yiisoft\Profile\Target\FileTarget::class,
 *             //...
 *         ],
 *         'targets.params' => [
 *             'log' => [
 *                 'enabled' => true,
 *                 'filename' => '@runtime/profiling/{date}-{time}.txt',
 *                 'dirMode' => 0775,
 *                 'exclude' => [],
 *                 'include' => [],
 *             ],
 *             // ...
 *         ],
 *     ],
 *     // ...
 * ];
 * ```
 */
final class FileTarget extends AbstractTarget
{
    /**
     * @var string file path or [path alias](guide:concept-aliases). File name may contain the placeholders,
     * which will be replaced by computed values. The supported placeholders are:
     *
     * - '{ts}' - profiling completion timestamp.
     * - '{date}' - profiling completion date in format 'ymd'.
     * - '{time}' - profiling completion time in format 'His'.
     *
     * The directory containing the file will be automatically created if not existing.
     * If target file is already exist it will be overridden.
     */
    private string $filename;

    /**
     * @var int the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    private int $dirMode;

    public function __construct(string $filename, int $dirMode = 0775)
    {
        $this->filename = $filename;
        $this->dirMode = $dirMode;
    }

    public function export(array $messages): void
    {
        $memoryPeakUsage = memory_get_peak_usage();

        // TODO: make sure it works with RoadRunner and alike servers
        $totalTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $text = "Total processing time: {$totalTime} ms; Peak memory: {$memoryPeakUsage} B. \n\n";

        $text .= implode("\n", array_map([$this, 'formatMessage'], $messages));

        $filename = $this->resolveFilename();

        if (file_exists($filename)) {
            FileHelper::unlink($filename);
        } else {
            $filePath = dirname($filename);

            if (!is_dir($filePath)) {
                FileHelper::createDirectory($filePath, $this->dirMode);
            }
        }

        file_put_contents($filename, $text);
    }

    /**
     * Resolves value of {@see filename} processing path alias and placeholders.
     *
     * @return string actual target filename.
     */
    private function resolveFilename(): string
    {
        return preg_replace_callback(
            '/{\\w+}/',
            static function (array $matches) {
                switch ($matches[0]) {
                    case '{ts}':
                        return time();
                    case '{date}':
                        return gmdate('ymd');
                    case '{time}':
                        return gmdate('His');
                }
                return $matches[0];
            },
            $this->filename
        );
    }

    /**
     * Formats a profiling message for display as a string.
     *
     * @param Message $message the profiling message to be formatted.
     * The message structure follows that in {@see Profiler::$messages}.
     *
     * @return string the formatted message.
     */
    private function formatMessage(Message $message): string
    {
        return date('Y-m-d H:i:s', (int)$message->context('beginTime'))
            . " [{$message->context('duration')} ms][{$message->context('memoryDiff')} B][{$message->level()}] {$message->message()}";
    }
}
