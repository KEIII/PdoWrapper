<?php

namespace KEIII\PdoWrapper\Interfaces;

interface PdoPaginatedResultInterface
{
    /**
     * Get a total count of items.
     *
     * @return int
     */
    public function getTotalCount();

    /**
     * Get items rows.
     *
     * @return array
     */
    public function getRows();

    /**
     * Wherever is next page.
     *
     * @return bool
     */
    public function isHasMore();
}
