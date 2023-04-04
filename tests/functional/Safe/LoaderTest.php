<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Csv\Safe;

use functional\Kiboko\Component\Flow\Csv\PipelineRunner;
use Kiboko\Component\Flow\Csv;
use Kiboko\Component\PHPUnitExtension\Assert\LoaderAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

final class LoaderTest extends TestCase
{
    use LoaderAssertTrait;

    private ?vfsStreamDirectory $fs = null;

    protected function setUp(): void
    {
        $this->fs = vfsStream::setup();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testFirstLineAsTitlesWithoutOptions()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent(<<<CSV
            firstname,lastname
            "Jean Pierre",Martin
            John,Doe
            Frank,O'hara

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file);

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function testFirstLineAsTitlesWithDelimiter()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent( <<<CSV
            firstname;lastname
            "Jean Pierre";Martin
            John;Doe
            Frank;O'hara

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file, delimiter: ';');

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function testFirstLineAsTitlesWithEnclosure()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent( <<<CSV
            firstname,lastname
            'Jean Pierre',Martin
            John,Doe
            Frank,'O''hara'

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file, enclosure: '\'');

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function testFirstLineAsTitlesWithEscape()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent(<<<CSV
            firstname,lastname,address
            "Jean Pierre",Martin,"main street, 42
            Burgtown 12345"
            John,Doe,"22nd street, 36
            Burgtown 12345"
            Frank,O'hara,"station ""42"" street, 42
            Burgtown 12345"

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file, escape: '\\', enclosure: '"');

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function testNoTitlesWithoutOptions()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent(<<<CSV
            "Jean Pierre",Martin
            John,Doe
            Frank,O'hara

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file, columns: ['firstname', 'lastname'], firstLineAsHeaders: false);

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function testNoTitlesWithDelimiter()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent(<<<CSV
            "Jean Pierre";Martin
            John;Doe
            Frank;O'hara

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file, delimiter: ';', columns: ['firstname', 'lastname'], firstLineAsHeaders: false);

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function testNoTitlesWithEnclosure()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent(<<<CSV
            'Jean Pierre',Martin
            John,Doe
            Frank,'O''hara'

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file, enclosure: '\'', columns: ['firstname', 'lastname'], firstLineAsHeaders: false);

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function testNoTitlesWithEscape()
    {
        $outputFile = new vfsStreamFile('output.csv');
        $this->fs->addChild($outputFile);

        $expectedFile = new vfsStreamFile('expected.csv');
        $expectedFile->setContent(<<<CSV
            "Jean Pierre",Martin,"main street, 42
            Burgtown 12345"
            John,Doe,"22nd street, 36
            Burgtown 12345"
            Frank,O'hara,"station ""42"" street, 42
            Burgtown 12345"

            CSV
        );
        $this->fs->addChild($expectedFile);

        $file = new \SplFileObject($outputFile->url(), 'w');

        $file->seek(0);

        $loader = new Csv\Safe\Loader($file, escape: '\\', enclosure: '"', columns: ['firstname', 'lastname', 'address'], firstLineAsHeaders: false);

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

        $this->assertFileEquals($expectedFile->url(), $outputFile->url());
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
