<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Csv\Safe;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\RejectionResultBucket;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @template-implements ExtractorInterface<array>
 */
readonly class Extractor implements ExtractorInterface
{
    public function __construct(
        private \SplFileObject $file,
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '\\',
        private ?array $columns = null,
        private LoggerInterface $logger = new NullLogger()
    ) {
    }

    /** @return iterable<AcceptanceResultBucket<array>|RejectionResultBucket<array|null>> */
    public function extract(): iterable
    {
        try {
            $this->cleanBom();

            if ($this->file->eof()) {
                return;
            }

            $this->file->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

            if (null === $this->columns) {
                $columns = $this->file->fgetcsv();
            } else {
                $columns = $this->columns;
            }
            $columnCount = null === $columns ? 0 : \count($columns);

            $currentLine = 0;
            while (!$this->file->eof()) {
                try {
                    $line = $this->file->fgetcsv();
                    if (false === $line) {
                        continue;
                    }
                    $cellCount = \count((array) $line);
                    ++$currentLine;

                    if ($cellCount > $columnCount) {
                        throw new \RuntimeException(strtr('The line %line% contains too much values: found %actual% values, was expecting %expected% values.', ['%line%' => $currentLine, '%expected%' => $columnCount, '%actual%' => $cellCount]));
                    }
                    if ($cellCount > $columnCount) {
                        throw new \RuntimeException(strtr('The line %line% does not contain the proper values count: found %actual% values, was expecting %expected% values.', ['%line%' => $currentLine, '%expected%' => $columnCount, '%actual%' => $cellCount]));
                    }

                    if (
                        \count((array) $line) == (null === $columns ? 0 : \count($columns))
                        && null !== ($result = array_combine($columns, $line))
                    ) {
                        yield new AcceptanceResultBucket(array_combine($columns, $line));
                    } else {
                        yield new RejectionResultBucket($line);
                    }
                } catch (\Throwable $exception) {
                    $this->logger->critical($exception->getMessage(), ['exception' => $exception]);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->emergency($exception->getMessage(), ['exception' => $exception]);
        }
    }

    public function cleanBom(): void
    {
        $bom = $this->file->fread(3);
        if (!preg_match('/^\\xEF\\xBB\\xBF$/', $bom)) {
            $this->file->seek(0);
        }
    }
}
