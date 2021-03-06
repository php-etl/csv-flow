<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Flow\Csv;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
use PHPUnit\Framework\TestCase;

final class ExtractorTest extends TestCase
{
    use PipelineAssertTrait;

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

        $extratctor = new Csv\FingersCrossed\Extractor($file);

        $this->assertPipelineExtractsLike(
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

        $extratctor = new Csv\FingersCrossed\Extractor($file, delimiter: ';');

        $this->assertPipelineExtractsLike(
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

        $extratctor = new Csv\FingersCrossed\Extractor($file, enclosure: '\'');

        $this->assertPipelineExtractsLike(
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
            $extratctor,
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

        $extratctor = new Csv\FingersCrossed\Extractor($file, enclosure: '\'', escape: '\'');

        $this->assertPipelineExtractsLike(
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

    public function testNoTitlesWithoutOptions()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            "Jean Pierre","Martin"
            "John","Doe"
            "Frank","O'hara"
            CSV);

        $file->seek(0);

        $extratctor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname']);

        $this->assertPipelineExtractsLike(
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

        $extratctor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname'], delimiter: ';');

        $this->assertPipelineExtractsLike(
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

    public function testNoTitlesWithEnclosure()
    {
        $file = new \SplFileObject('php://temp', 'r+');

        $file->fwrite(<<<CSV
            'Jean Pierre','Martin'
            'John','Doe'
            'Frank','O\'hara'
            CSV);

        $file->seek(0);

        $extratctor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname'], enclosure: '\'');

        $this->assertPipelineExtractsLike(
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
            $extratctor,
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

        $extratctor = new Csv\FingersCrossed\Extractor($file, columns: ['firstname', 'lastname'], enclosure: '\'', escape: '\'');

        $this->assertPipelineExtractsLike(
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
}
