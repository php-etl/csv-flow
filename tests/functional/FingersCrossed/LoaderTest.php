<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Csv\FingersCrossed;

use functional\Kiboko\Component\Flow\Csv\PipelineRunner;
use Kiboko\Component\Flow\Csv;
use Kiboko\Component\PHPUnitExtension\Assert\LoaderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class LoaderTest extends TestCase
{
    use LoaderAssertTrait;

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
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            firstname,lastname
            "Jean Pierre",Martin
            John,Doe
            Frank,O'hara

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file);

        $this->assertLoaderLoadsLike(
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
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function testFirstLineAsTitlesWithDelimiter()
    {
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            firstname;lastname
            "Jean Pierre";Martin
            John;Doe
            Frank;O'hara

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file, delimiter: ';');

        $this->assertLoaderLoadsLike(
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
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function testFirstLineAsTitlesWithEnclosure()
    {
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            firstname,lastname
            'Jean Pierre',Martin
            John,Doe
            Frank,'O''hara'

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file, enclosure: '\'');

        $this->assertLoaderLoadsLike(
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
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function testFirstLineAsTitlesWithEscape()
    {
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            firstname,lastname,address
            "Jean Pierre",Martin,"main street, 42
            Burgtown 12345"
            John,Doe,"22nd street, 36
            Burgtown 12345"
            Frank,O'hara,"station ""42"" street, 42
            Burgtown 12345"

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file, escape: '\\', enclosure: '"');

        $this->assertLoaderLoadsLike(
            [
                [
                    'firstname' => 'Jean Pierre',
                    'lastname' => 'Martin',
                    'address' => "main street, 42\nBurgtown 12345",
                ],
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'address' => "22nd street, 36\nBurgtown 12345",
                ],
                [
                    'firstname' => 'Frank',
                    'lastname' => 'O\'hara',
                    'address' => "station \"42\" street, 42\nBurgtown 12345",
                ],
            ],
            [
                [
                    'firstname' => 'Jean Pierre',
                    'lastname' => 'Martin',
                    'address' => "main street, 42\nBurgtown 12345",
                ],
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'address' => "22nd street, 36\nBurgtown 12345",
                ],
                [
                    'firstname' => 'Frank',
                    'lastname' => 'O\'hara',
                    'address' => "station \"42\" street, 42\nBurgtown 12345",
                ],
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function testNoTitlesWithoutOptions()
    {
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            "Jean Pierre",Martin
            John,Doe
            Frank,O'hara

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file, columns: ['firstname', 'lastname'], firstLineAsHeaders: false);

        $this->assertLoaderLoadsLike(
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
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function testNoTitlesWithDelimiter()
    {
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            "Jean Pierre";Martin
            John;Doe
            Frank;O'hara

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file, delimiter: ';', columns: ['firstname', 'lastname'], firstLineAsHeaders: false);

        $this->assertLoaderLoadsLike(
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
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function testNoTitlesWithEnclosure()
    {
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            'Jean Pierre',Martin
            John,Doe
            Frank,'O''hara'

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file, enclosure: '\'', columns: ['firstname', 'lastname'], firstLineAsHeaders: false);

        $this->assertLoaderLoadsLike(
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
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function testNoTitlesWithEscape()
    {
        $file = new \SplFileObject('vfs://output.csv', 'w');

        file_put_contents('vfs://expected.csv', <<<CSV
            "Jean Pierre",Martin,"main street, 42
            Burgtown 12345"
            John,Doe,"22nd street, 36
            Burgtown 12345"
            Frank,O'hara,"station ""42"" street, 42
            Burgtown 12345"

            CSV);

        $file->seek(0);

        $loader = new Csv\FingersCrossed\Loader($file, escape: '\\', enclosure: '"', columns: ['firstname', 'lastname'], firstLineAsHeaders: false);

        $this->assertLoaderLoadsLike(
            [
                [
                    'firstname' => 'Jean Pierre',
                    'lastname' => 'Martin',
                    'address' => "main street, 42\nBurgtown 12345",
                ],
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'address' => "22nd street, 36\nBurgtown 12345",
                ],
                [
                    'firstname' => 'Frank',
                    'lastname' => 'O\'hara',
                    'address' => "station \"42\" street, 42\nBurgtown 12345",
                ],
            ],
            [
                [
                    'firstname' => 'Jean Pierre',
                    'lastname' => 'Martin',
                    'address' => "main street, 42\nBurgtown 12345",
                ],
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'address' => "22nd street, 36\nBurgtown 12345",
                ],
                [
                    'firstname' => 'Frank',
                    'lastname' => 'O\'hara',
                    'address' => "station \"42\" street, 42\nBurgtown 12345",
                ],
            ],
            $loader,
        );

        $this->assertFileEquals('vfs://expected.csv', 'vfs://output.csv');
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
