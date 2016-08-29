<?php

namespace KEIII\PdoWrapper;

class PdoPaginatedResult
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
     * @return int
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
    public function setTotalCount($totalCount)
    {
        $this->totalCount = (int)$totalCount;

        return $this;
    }

    /**
     * @return array
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
    public function setRows(array $rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @return bool
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
    public function setHasMore($hasMore)
    {
        $this->hasMore = (bool)$hasMore;

        return $this;
    }
}
