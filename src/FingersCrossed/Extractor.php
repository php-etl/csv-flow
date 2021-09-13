<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Extractor implements ExtractorInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private \SplFileObject $file,
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '\\',
        private ?array $columns = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function extract(): iterable
    {
        try {
            $this->cleanBom();

            if ($this->file->eof()) {
                return;
            }

            $this->file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

            if ($this->columns === null) {
                $columns = $this->file->fgetcsv();
            } else {
                $columns = $this->columns;
            }
            $columnCount = count($columns);

            while (!$this->file->eof()) {
                try {
                    $line = $this->file->fgetcsv();
                    $cellCount = count($line);

                    if ($cellCount > $columnCount) {
                        $line = array_slice($line, 0, $columnCount, true);
                    } elseif ($cellCount > $columnCount) {
                        $line = array_pad($line, $columnCount - $cellCount, null);
                    }

                    yield new AcceptanceResultBucket(array_combine($columns, $line));
                } catch (\Throwable $exception) {
                    $this->logger?->critical($exception->getMessage(), ['exception' => $exception]);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger?->emergency($exception->getMessage(), ['exception' => $exception]);
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
