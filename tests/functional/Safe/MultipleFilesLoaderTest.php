<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Csv\Safe;

use Kiboko\Component\Flow\Csv;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class MultipleFilesLoaderTest extends TestCase
{
    use PipelineAssertTrait;

    private ?FileSystem $fs = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;
    }

    public function testFirstLineAsTitlesWithoutOptions()
    {
        file_put_contents('vfs://expected-1.csv', <<<CSV
            firstname,lastname
            "Jean Pierre",Martin
            John,Doe

            CSV);

        file_put_contents('vfs://expected-2.csv', <<<CSV
            firstname,lastname
            Frank,O'hara
            Hiroko,Froncillo

            CSV);

        $loader = new Csv\Safe\MultipleFilesLoader('vfs://','SKU_%06d.csv', 3);

        $this->assertPipelineLoadsLike(
            [
                [
                    'firstname' => 'Jean Pierre',
                    'lastname' => 'Martin',
                ],
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                ],
                [
                    'firstname' => 'Frank',
                    'lastname' => 'O\'hara',
                ],
                [
                    'firstname' => 'Hiroko',
                    'lastname' => 'Froncillo',
                ],
            ],
            [
                [
                    'firstname' => 'Jean Pierre',
                    'lastname' => 'Martin',
                ],
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                ],
                [
                    'firstname' => 'Frank',
                    'lastname' => 'O\'hara',
                ],
                [
                    'firstname' => 'Hiroko',
                    'lastname' => 'Froncillo',
                ],
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected-1.csv', 'vfs://SKU_000001.csv');
        $this->assertFileEquals('vfs://expected-2.csv', 'vfs://SKU_000002.csv');
    }
}
