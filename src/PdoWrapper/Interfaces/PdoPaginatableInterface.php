<?php

namespace KEIII\PdoWrapper\Interfaces;

interface PdoPaginatableInterface extends PdoWrapperInterface
{
    /**
     * Get a paginated result.
     *
     * @param PdoQueryInterface $query  The query without limit and offset
     * @param int               $offset The offset of the first row to return
     * @param int               $limit  The maximum number of rows to return
     *
     * @return PdoPaginatedResultInterface
     */
    public function paginatedResult(PdoQueryInterface $query, $limit = 10, $offset = 0);
}
