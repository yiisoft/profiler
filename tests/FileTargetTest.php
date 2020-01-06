<?php
declare(strict_types=1);

namespace Yiisoft\Profiler\Tests;

use Yiisoft\Files\FileHelper;
use Yiisoft\Profiler\FileTarget;
use Yiisoft\Profiler\Profiler;

class FileTargetTest extends TestCase
{
    /**
     * @var string
     */
    protected $testFilePath;

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

        $profiler->addTarget($this->factory->create([
            '__class' => FileTarget::class,
            'setFilename()' => [$filename],
        ]));

        $profiler->begin('test-export', ['category' => 'test-category']);
        $profiler->end('test-export', ['category' => 'test-category']);

        $profiler->flush();

        $this->assertFileExists($filename);

        $fileContent = file_get_contents($filename);
        $this->assertStringContainsString('[test-category] test-export', $fileContent);
    }
}
