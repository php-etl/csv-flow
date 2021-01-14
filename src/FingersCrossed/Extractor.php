<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Extractor implements ExtractorInterface, LoggerAwareInterface
{
    private \SplFileObject $file;
    private string $delimiter;
    private string $enclosure;
    private string $escape;
    private ?LoggerInterface $logger;

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
        try {
            $this->cleanBom();

            if ($this->file->eof()) {
                return;
            }

            $this->file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

            $columns = $this->file->fgetcsv();
            $columnCount = count($columns);

            while (!$this->file->eof()) {
                try {
                    $line = $this->file->fgetcsv();
                    $cellCount = count($line);

                    if ($cellCount > $columnCount) {
                        $line = array_slice($line, 0, $columnCount, true);
                    } else if ($cellCount > $columnCount) {
                        $line = array_pad($line, $columnCount - $cellCount, null);
                    }

                    yield array_combine($columns, $line);
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

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }
}
