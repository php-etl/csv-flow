<?php

namespace Kiboko\Component\Flow\Csv\Safe;

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
        $headers = [];
        while (true) {
            try {
                $line = yield;

                if ($isFirstLine === true) {
                    $this->file->fputcsv($headers = array_keys($line), $this->delimiter, $this->enclosure, $this->escape);
                    $isFirstLine = false;
                }

                $this->file->fputcsv($this->orderColumns($headers, $line), $this->delimiter, $this->enclosure, $this->escape);

                yield new AcceptanceResultBucket($line);
            } catch (\Throwable $exception) {
                $this->logger?->critical($exception->getMessage(), ['exception' => $exception]);
                yield new EmptyResultBucket();
            }
        }
    }

    private function orderColumns(array $headers, array $line)
    {
        $result = [];
        foreach ($headers as $cell) {
            $result[$cell] = $line[$cell] ?? null;
        }

        return $result;
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
