<?php

namespace Kiboko\Component\ETL\Flow\SPL\CSV\Safe;

use Kiboko\Component\ETL\Bucket\AcceptanceResultBucket;
use Kiboko\Component\ETL\Contracts\LoaderInterface;

class Loader implements LoaderInterface
{
    /** @var \SplFileObject */
    private $file;
    /** @var string */
    private $delimiter;
    /** @var string */
    private $enclosure;
    /** @var string */
    private $escape;

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
            $line = yield;

            if ($isFirstLine === true) {
                $this->file->fputcsv($headers = array_keys($line), $this->delimiter, $this->enclosure, $this->escape);
                $isFirstLine = false;
            }

            $this->file->fputcsv($this->orderColumns($headers, $line), $this->delimiter, $this->enclosure, $this->escape);

            yield new AcceptanceResultBucket($line);
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
}
