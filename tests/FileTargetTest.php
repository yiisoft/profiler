<?php

declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Files\FileHelper;
use Yiisoft\Profiler\Target\FileTarget;
use Yiisoft\Profiler\Message;
use Yiisoft\Profiler\Profiler;

use function dirname;

final class FileTargetTest extends TestCase
{
    protected string $testFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testFilePath = 'tests/data';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (!empty($this->testFilePath)) {
            FileHelper::removeDirectory($this->testFilePath);
        }
    }

    public function testExport(): void
    {
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'test.txt';

        $target = new FileTarget($filename, microtime(true));
        $profiler = new Profiler($this->logger, [$target]);

        $profiler->begin('test-export', ['category' => 'test-category']);
        $profiler->end('test-export', ['category' => 'test-category']);

        $profiler->flush();

        $this->assertFileExists($filename);

        $fileContent = file_get_contents($filename);
        $this->assertStringContainsString('[test-category] test-export', $fileContent);
    }

    public function testExportWithExistFile(): void
    {
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'test.txt';
        $filePath = dirname($filename);
        if (!is_dir($filePath)) {
            FileHelper::ensureDirectory($filePath);
        }

        $target = new FileTarget($filename, microtime(true));

        $testData = 'test';
        file_put_contents($filename, $testData);

        $message = new Message('test-category', 'test-export');

        $target->export([$message]);

        $this->assertFileExists($filename);

        $fileContent = file_get_contents($filename);
        $this->assertNotSame($testData, $fileContent);
        $this->assertStringContainsString('[test-category] test-export', $fileContent);
    }

    public function testExportWithResolveFilename(): void
    {
        $profiler = new Profiler($this->logger);

        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'test-{date}-{time}-{ts}-{test}.txt';
        $filePath = dirname($filename);
        if (!is_dir($filePath)) {
            FileHelper::ensureDirectory($filePath);
        }

        $target = new FileTarget($filename, microtime(true));

        $resolvedFilename = $this->invokeMethod($target, 'resolveFilename');

        file_put_contents($resolvedFilename, 'test');

        $profiler->begin('test-export', ['category' => 'test-category']);
        $profiler->end('test-export', ['category' => 'test-category']);

        $target->export($profiler->getMessages());

        $this->assertFileExists($resolvedFilename);
        $expectedFilename = $this->testFilePath . DIRECTORY_SEPARATOR . 'test-' . gmdate('ymd') . '-'
            . gmdate('His') . '-' . time() . '-{test}.txt';
        $this->assertEquals($expectedFilename, $resolvedFilename);

        $fileContent = file_get_contents($resolvedFilename);
        $this->assertStringContainsString('[test-category] test-export', $fileContent);
    }
}
