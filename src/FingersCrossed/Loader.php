<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Loader implements LoaderInterface
{
    public function __construct(private readonly \SplFileObject $file, private readonly string $delimiter = ',', private readonly string $enclosure = '"', private readonly string $escape = '\\', private readonly ?array $columns = null, private readonly bool $firstLineAsHeaders = true, private readonly LoggerInterface $logger = new NullLogger())
    {
    }

    /**
     * @return \Generator
     */
    public function load(): \Generator
    {
        $line = yield new EmptyResultBucket();
        if ($this->columns !== null) {
            $headers = $this->columns;
        } else {
            $headers = array_keys($line);
        }
        if ($this->firstLineAsHeaders === true) {
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
