<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Csv\Safe;

use Kiboko\Component\Flow\Csv;
use Kiboko\Component\PHPUnitExtension\Assert\ExtractorAssertTrait;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
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

        $extractor = new Csv\Safe\Extractor($file);

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

        $extractor = new Csv\Safe\Extractor($file, delimiter: ';');

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

        $extractor = new Csv\Safe\Extractor($file, enclosure: '\'');

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

        $extractor = new Csv\Safe\Extractor($file, enclosure: '\'', escape: '\'');

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

        $extractor = new Csv\Safe\Extractor($file, columns: ['firstname', 'lastname']);

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
            $extratctor,
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

        $extractor = new Csv\Safe\Extractor($file, columns: ['firstname', 'lastname'], delimiter: ';');

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

        $extractor = new Csv\Safe\Extractor($file, columns: ['firstname', 'lastname'], enclosure: '\'');

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

        $extractor = new Csv\Safe\Extractor($file, columns: ['firstname', 'lastname'], enclosure: '\'', escape: '\'');

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
}
