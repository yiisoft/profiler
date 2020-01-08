<?php
declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Files\FileHelper;
use Yiisoft\Profiler\FileTarget;
use Yiisoft\Profiler\Profiler;

class FileTargetTest extends TestCase
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
        $profiler = new Profiler($this->logger);

        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'test.txt';

        $target = new FileTarget();
        $target->setFilename($filename);
        $profiler->addTarget($target);

        $profiler->begin('test-export', ['category' => 'test-category']);
        $profiler->end('test-export', ['category' => 'test-category']);

        $profiler->flush();

        $this->assertFileExists($filename);

        $fileContent = file_get_contents($filename);
        $this->assertStringContainsString('[test-category] test-export', $fileContent);
    }
}
