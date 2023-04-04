<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Csv\FingersCrossed;

use functional\Kiboko\Component\Flow\Csv\PipelineRunner;
use Kiboko\Component\Flow\Csv;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorAssertTrait;
use Kiboko\Contract\Pipeline\PipelineRunnerInterface;
use PHPUnit\Framework\TestCase;

final class ExtractorTest extends TestCase
{
    use ExtractorAssertTrait;

    public function testFirstLineAsTitlesWithoutOptions()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            "firstname","lastname"
            "Jean Pierre","Martin"
            "John","Doe"
            "Frank","O'hara"
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file);

        $this->assertExtractorExtractsLike(
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
            $extractor,
        );
    }

    public function testFirstLineAsTitlesWithDelimiter()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            "firstname";"lastname"
            "Jean Pierre";"Martin"
            "John";"Doe"
            "Frank";"O'hara"
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file, delimiter: ';');

        $this->assertExtractorExtractsLike(
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
            $extractor,
        );
    }

    public function testFirstLineAsTitlesWithEnclosure()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            'firstname','lastname'
            'Jean Pierre','Martin'
            'John','Doe'
            'Frank','O\'hara'
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file, enclosure: '\'');

        $this->assertExtractorExtractsLike(
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
                    'lastname' => 'O\\\'hara',
                ],
            ],
            $extractor,
        );
    }

    public function testFirstLineAsTitlesWithEscape()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            'firstname','lastname'
            'Jean Pierre','Martin'
            'John','Doe'
            'Frank','O''hara'
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file, enclosure: '\'', escape: '\'');

        $this->assertExtractorExtractsLike(
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
            $extractor,
        );
    }

    public function testNoTitlesWithoutOptions()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            "Jean Pierre","Martin"
            "John","Doe"
            "Frank","O'hara"
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname']);

        $this->assertExtractorExtractsLike(
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
            $extractor,
        );
    }

    public function testNoTitlesWithDelimiter()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            "Jean Pierre";"Martin"
            "John";"Doe"
            "Frank";"O'hara"
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname'], delimiter: ';');

        $this->assertExtractorExtractsLike(
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
            $extractor,
        );
    }

    public function testNoTitlesWithEnclosure()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            'Jean Pierre','Martin'
            'John','Doe'
            'Frank','O\'hara'
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname'], enclosure: '\'');

        $this->assertExtractorExtractsLike(
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
                    'lastname' => 'O\\\'hara',
                ],
            ],
            $extractor,
        );
    }

    public function testNoTitlesWithEscape()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            'Jean Pierre','Martin'
            'John','Doe'
            'Frank','O''hara'
            CSV);

        $file->seek(0);

        $extractor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname'], enclosure: '\'', escape: '\'');

        $this->assertExtractorExtractsLike(
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
            $extractor,
        );
    }

    public function pipelineRunner(): PipelineRunnerInterface
    {
        return new PipelineRunner();
    }
}
