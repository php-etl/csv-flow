<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

readonly class Loader implements LoaderInterface
{
    public function __construct(
        private \SplFileObject $file,
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '\\',
        private ?array $columns = null,
        private bool $firstLineAsHeaders = true,
        private LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function load(): \Generator
    {
        $line = yield new EmptyResultBucket();
        if (null !== $this->columns) {
            $headers = $this->columns;
        } else {
            $headers = array_keys($line);
        }
        if (true === $this->firstLineAsHeaders) {
            $this->file->fputcsv($headers, $this->delimiter, $this->enclosure, $this->escape);
        }

        while ($line) {
            try {
                $this->file->fputcsv($line, $this->delimiter, $this->enclosure, $this->escape);

                $line = yield new AcceptanceResultBucket($line);
            } catch (\Throwable $exception) {
                $this->logger->critical($exception->getMessage(), ['exception' => $exception]);
                $line = yield new EmptyResultBucket();
            }
        }
    }
}
