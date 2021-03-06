<?php

namespace KEIII\PdoWrapper\Interfaces;

use KEIII\PdoWrapper\Exceptions\PdoWrapperException;

interface PdoWrapperInterface
{
    /**
     * Create a connection.
     *
     * @throws PdoWrapperException
     */
    public function connect();

    /**
     * Close and create new connection.
     */
    public function reconnect();

    /**
     * Close the connection.
     */
    public function close();

    /**
     * Whether there is a connection.
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Get a PDO instance.
     *
     * @return \PDO
     */
    public function getPdo();

    /**
     * Read the data from the database.
     *
     * @param PdoQueryInterface $query
     *
     * @return PdoDataReaderInterface
     *
     * @throws PdoWrapperException
     */
    public function read(PdoQueryInterface $query);

    /**
     * Write the data to the database.
     *
     * @param PdoQueryInterface $query
     *
     * @throws PdoWrapperException
     */
    public function write(PdoQueryInterface $query);

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
}
