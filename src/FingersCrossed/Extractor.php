<?php

namespace Kiboko\Component\Flow\Csv\FingersCrossed;

use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\RejectionResultBucket;
use Kiboko\Contract\Pipeline\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @template-implements ExtractorInterface<array>
 */
class Extractor implements ExtractorInterface
{
    public function __construct(private readonly \SplFileObject $file, private readonly string $delimiter = ',', private readonly string $enclosure = '"', private readonly string $escape = '\\', private readonly ? array $columns = null, private readonly LoggerInterface $logger = new NullLogger())
    {
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

            if ($this->columns === null) {
                $columns = $this->file->fgetcsv();
            } else {
                $columns = $this->columns;
            }
            $columnCount = $columns === null ? 0 : count($columns);

            while (!$this->file->eof()) {
                try {
                    $line = $this->file->fgetcsv();
                    if ($line === false) {
                        continue;
                    }
                    $cellCount = count((array) $line);

                    if ($cellCount > $columnCount) {
                        $line = array_slice($line, 0, $columnCount, true);
                    } elseif ($cellCount > $columnCount) {
                        $line = array_pad($line, $columnCount - $cellCount, null);
                    }

                    $result = array_combine($columns, $line);
                    if (!is_array($result)) {
                        yield new RejectionResultBucket($line);
                    } else {
                        yield new AcceptanceResultBucket($result);
                    }
                } catch (\Throwable $exception) {
                    $this->logger->critical($exception->getMessage(), ['exception' => $exception]);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->emergency($exception->getMessage(), ['exception' => $exception]);
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
