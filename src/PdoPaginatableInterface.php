<?php

namespace KEIII\PdoWrapper;

interface PdoPaginatableInterface
{
    /**
     * Get a paginated result.
     *
     * @param PdoQuery $query  The query without limit and offset
     * @param int      $offset The offset of the first row to return
     * @param int      $limit  The maximum number of rows to return
     *
     * @return PdoPaginatedResult
     */
    public function paginatedResult(PdoQuery $query, $limit = 10, $offset = 0);
}
