<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Contract\ETL\Pipeline\ExtractorInterface;

class Extractor implements ExtractorInterface
{
    /** @var \SplFileObject */
    private $file;
    /** @var string */
    private $delimiter;
    /** @var string */
    private $enclosure;
    /** @var string */
    private $escape;

    public function __construct(
        \SplFileObject $file,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\'
    ) {
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }

    public function extract(): iterable
    {
        $this->cleanBom();

        if ($this->file->eof()) {
            return;
        }

        $this->file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

        $columns = $this->file->fgetcsv();
        $columnCount = count($columns);

        while (!$this->file->eof()) {
            $line = $this->file->fgetcsv();
            $cellCount = count($line);

            if ($cellCount > $columnCount) {
                $line = array_slice($line, 0, $columnCount, true);
            } else if ($cellCount > $columnCount) {
                $line = array_pad($line, $columnCount - $cellCount, null);
            }

            yield array_combine($columns, $line);
        }
    }

    public function cleanBom()
    {
        $bom = $this->file-> fread(3);
        if (!preg_match('/^\\xEF\\xBB\\xBF$/', $bom)) {
            $this->file-> seek(0);
        }
    }
}
