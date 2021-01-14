<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Loader implements LoaderInterface, LoggerAwareInterface
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

    public function load(): \Generator
    {
        $isFirstLine = true;
        while (true) {
            $line = yield;

            if ($isFirstLine === true) {
                $this->file->fputcsv(array_keys($line), $this->delimiter, $this->enclosure, $this->escape);
                $isFirstLine = false;
            }

            $this->file->fputcsv($line, $this->delimiter, $this->enclosure, $this->escape);

            yield new AcceptanceResultBucket($line);
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
