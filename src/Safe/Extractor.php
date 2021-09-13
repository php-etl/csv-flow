<?php

namespace Kiboko\Component\Flow\Csv\Safe;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
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

            $currentLine = 0;
            while (!$this->file->eof()) {
                try {
                    $line = $this->file->fgetcsv();
                    $cellCount = count($line);
                    ++$currentLine;

                    if ($cellCount > $columnCount) {
                        throw new \RuntimeException(strtr(
                            'The line %line% contains too much values: found %actual% values, was expecting %expected% values.',
                            [
                                '%line%' => $currentLine,
                                '%expected%' => $columnCount,
                                '%actual%' => $cellCount,
                            ]
                        ));
                    } elseif ($cellCount > $columnCount) {
                        throw new \RuntimeException(strtr(
                            'The line %line% does not contain the proper values count: found %actual% values, was expecting %expected% values.',
                            [
                                '%line%' => $currentLine,
                                '%expected%' => $columnCount,
                                '%actual%' => $cellCount,
                            ]
                        ));
                    }

                    if (count($line) == count($columns)) {
                        yield new AcceptanceResultBucket(array_combine($columns, $line));
                    } else {
                        yield new EmptyResultBucket();
                    }
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
