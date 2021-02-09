<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Loader implements LoaderInterface, LoggerAwareInterface
{
    private \SplFileObject $file;
    private string $delimiter;
    private string $enclosure;
    private string $escape;
    private ?LoggerInterface $logger = null;

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
        $line = yield;
        $this->file->fputcsv(array_keys($line), $this->delimiter, $this->enclosure, $this->escape);

        while (true) {
            try {
                $line = yield new AcceptanceResultBucket($line);

                $this->file->fputcsv($line, $this->delimiter, $this->enclosure, $this->escape);
            } catch (\Throwable $exception) {
                $this->logger?->critical($exception->getMessage(), ['exception' => $exception]);
                yield new EmptyResultBucket();
            }
        }
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }
}
