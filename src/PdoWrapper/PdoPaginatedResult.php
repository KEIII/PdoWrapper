<?php

namespace KEIII\PdoWrapper;

use KEIII\PdoWrapper\Interfaces\PdoPaginatedResultInterface;

class PdoPaginatedResult implements PdoPaginatedResultInterface
{
    /**
     * The total count of founded rows without limit.
     *
     * @var int
     */
    private $totalCount = 0;

    /**
     * If has more rows for next page.
     *
     * @var bool
     */
    private $hasMore = false;

    /**
     * The array of founded rows with offset and limit.
     *
     * @var array
     */
    private $rows = [];

    /**
     * Constructor.
     *
     * @param array $rows
     * @param int   $totalCount
     * @param int   $hasMore
     */
    public function __construct(array $rows, $totalCount, $hasMore)
    {
        $this->setRows($rows);
        $this->setTotalCount($totalCount);
        $this->setHasMore($hasMore);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     *
     * @return $this
     */
    private function setTotalCount($totalCount)
    {
        $this->totalCount = (int)$totalCount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param array $rows
     *
     * @return $this
     */
    private function setRows(array $rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isHasMore()
    {
        return $this->hasMore;
    }

    /**
     * @param bool $hasMore
     *
     * @return $this
     */
    private function setHasMore($hasMore)
    {
        $this->hasMore = (bool)$hasMore;

        return $this;
    }
}
