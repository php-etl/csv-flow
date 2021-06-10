<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MultipleFilesLoader implements LoaderInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private string $directory_path,
        private string $pattern,
        private int $numberLines,
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '\\',
        private ?array $columns = null,
        private bool $firstLineAsHeaders = true,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function load(): \Generator
    {
        $line = yield;

        $currentLine = 0;
        $currentFileNumber = 1;

        do {
            if ($currentLine < $this->numberLines) {
                $file = new \SplFileObject($this->directory_path. '/'. sprintf($this->pattern, $currentFileNumber), 'w');

                if ($this->columns !== null) {
                    $headers = $this->columns;
                } else {
                    $headers = array_keys($line);
                }
                if ($this->firstLineAsHeaders === true) {
                    $file->fputcsv($headers, $this->delimiter, $this->enclosure, $this->escape);
                    $currentLine++;
                }

                while ($currentLine < $this->numberLines) {
                    try {
                        $file->fputcsv($line, $this->delimiter, $this->enclosure, $this->escape);
                        $line = yield new AcceptanceResultBucket($line);
                    } catch (\Throwable $exception) {
                        $this->logger?->critical($exception->getMessage(), ['exception' => $exception]);
                        $line = yield new EmptyResultBucket();
                    }
                    $currentLine++;
                }
            }
            if ($currentLine >= $this->numberLines) {
                $currentLine = 0;
            }
        } while ($currentFileNumber++);
    }
}
