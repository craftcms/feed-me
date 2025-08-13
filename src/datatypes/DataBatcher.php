<?php


namespace craft\feedme\datatypes;

use craft\base\Batchable;

class DataBatcher implements Batchable
{
    public function __construct(
        private array $data,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getSlice(int $offset, int $limit): iterable
    {
        $slice = $this->data;

        if ($offset) {
            $slice = array_slice($slice, $offset);
        }

        if ($limit) {
            $slice = array_slice($slice, 0, $limit);
        }

        return $slice;
    }
}
