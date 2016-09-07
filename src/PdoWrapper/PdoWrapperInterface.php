<?php

namespace KEIII\PdoWrapper;

interface PdoWrapperInterface
{
    /**
     * Get a PDO instance.
     *
     * @return \PDO
     *
     * @throws PdoWrapperException
     */
    public function getPdo();

    /**
     * Read the data from the database.
     *
     * @param PdoQuery $query
     *
     * @return PdoDataReader
     *
     * @throws PdoWrapperException
     */
    public function read(PdoQuery $query);

    /**
     * Write the data to the database.
     *
     * @param PdoQuery $query
     *
     * @throws PdoWrapperException
     */
    public function write(PdoQuery $query);

    /**
     * Begin a transaction.
     *
     * @throws PdoWrapperException
     */
    public function beginTransaction();

    /**
     * Roll back a transaction.
     *
     * @throws PdoWrapperException
     */
    public function rollBack();

    /**
     * Commit a transaction.
     *
     * @throws PdoWrapperException
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
     * Close the connection.
     */
    public function close();
}
