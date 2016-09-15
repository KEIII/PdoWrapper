<?php

namespace KEIII\PdoWrapper\Interfaces;

interface PdoDataReaderInterface
{
    /**
     * Read data as generator.
     *
     * @return \Generator
     */
    public function asGenerator();

    /**
     * Read data as array.
     *
     * @return array
     */
    public function asArray();

    /**
     * Get first row.
     *
     * @return array|false
     */
    public function getFirst();

    /**
     * Read data as column.
     *
     * @param string $column
     *
     * @return array
     */
    public function asColumn($column = 'id');
}
