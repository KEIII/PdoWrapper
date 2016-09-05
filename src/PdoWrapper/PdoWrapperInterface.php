<?php

namespace KEIII\PdoWrapper;

interface PdoWrapperInterface
{
    /**
     * Read data from the database.
     *
     * @param PdoQuery $query
     *
     * @return PdoDataReader
     */
    public function read(PdoQuery $query);

    /**
     * Write data to the database.
     *
     * @param PdoQuery $query
     */
    public function write(PdoQuery $query);

    /**
     * Begin a transaction.
     */
    public function beginTransaction();

    /**
     * Roll back a transaction.
     */
    public function rollBack();

    /**
     * Commit a transaction.
     */
    public function commit();

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string $name Name of the sequence object from which the ID should be returned
     *
     * @return int|string
     */
    public function lastInsertId($name = 'id');

    /**
     * Close the connections.
     */
    public function close();
}
