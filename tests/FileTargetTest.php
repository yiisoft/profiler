<?php
namespace Yiisoft\Profiler\Tests;

use PHPUnit\Framework\TestCase;
use yii\helpers\Yii;
use Yiisoft\Files\FileHelper;
use Yiisoft\Profiler\FileTarget;
use Yiisoft\Profiler\Profiler;

class FileTargetTest extends TestCase
{
    /**
     * @var string
     */
    protected $testFilePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
        $this->testFilePath = Yii::getAlias('@runtime/test-profile');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (!empty($this->testFilePath)) {
            FileHelper::removeDirectory($this->testFilePath);
        }
    }

    public function testExport()
    {
        $profiler = new Profiler();

        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'test.txt';
        $profiler->addTarget($this->factory->create([
            '__class' => FileTarget::class,
            'filename' => $filename,
        ]));

        $profiler->begin('test-export', ['category' => 'test-category']);
        $profiler->end('test-export', ['category' => 'test-category']);
        $profiler->flush();

        $this->assertTrue(file_exists($filename));

        $fileContent = file_get_contents($filename);
        $this->assertContains('[test-category] test-export', $fileContent);
    }
}
