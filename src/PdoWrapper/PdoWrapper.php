<?php

namespace KEIII\PdoWrapper;

class PdoWrapper implements PdoWrapperInterface
{
    /**
     * @var \PDO|null
     */
    private $pdo;

    /**
     * @var int
     */
    private $transactionCount = 0;

    /**
     * @var \PDOStatement[]
     */
    private $cachedStatements = [];

    /**
     * @var string
     */
    private $dsn;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param string      $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array|null  $options
     */
    public function __construct($dsn, $username = null, $password = null, array $options = null)
    {
        $this->dsn = (string)$dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options ?: [];

        $this->connect();
    }

    /**
     * Create a connection.
     */
    private function connect()
    {
        // close a previous connection
        $this->close();

        try {
            $this->pdo = new \PDO(
                $this->dsn,
                null !== $this->username ? (string)$this->username : null,
                null !== $this->password ? (string)$this->password : null,
                array_replace(static::getDefaultOptions(), $this->options)
            );
        } catch (\PDOException $ex) {
            throw new PdoWrapperException('Connection failed.', null, null, $ex);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPdo()
    {
        if (!$this->pdo instanceof \PDO) {
            throw new PdoWrapperException('Connection is closed.');
        }

        return $this->pdo;
    }

    /**
     * @return array
     */
    protected static function getDefaultOptions()
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function read(PdoQuery $query)
    {
        return new PdoDataReader($this->execute($query));
    }

    /**
     * {@inheritdoc}
     */
    public function write(PdoQuery $query)
    {
        $statement = $this->execute($query);
        $statement->closeCursor();
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        if (++$this->transactionCount === 1) {
            $pdo = $this->getPdo();

            try {
                if (!$pdo->beginTransaction()) {
                    $ex = new PdoWrapperException('Initiates a transaction failure.');
                    $ex->errorInfo = $pdo->errorInfo();
                    throw $ex;
                }
            } catch (\PDOException $ex) {
                if ($ex instanceof PdoWrapperException) {
                    throw $ex;
                } else {
                    $wrapperEx = new PdoWrapperException($ex->getMessage(), null, $ex->getCode(), $ex);
                    $wrapperEx->errorInfo = $ex->errorInfo;
                    throw $wrapperEx;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        $this->transactionCount = 0;

        try {
            $this->getPdo()->rollBack();
        } catch (\PDOException $ex) {
            // ignore
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if ($this->transactionCount <= 0) {
            throw new PdoWrapperException('No transaction found.');
        }

        if (--$this->transactionCount === 0) {
            $pdo = $this->getPdo();

            try {
                if (!$pdo->commit()) {
                    $ex = new PdoWrapperException('Commits a transaction failure.');
                    $ex->errorInfo = $pdo->errorInfo();
                    throw $ex;
                }
            } catch (\PDOException $ex) {
                if ($ex instanceof PdoWrapperException) {
                    throw $ex;
                } else {
                    $wrapperEx = new PdoWrapperException($ex->getMessage(), null, $ex->getCode(), $ex);
                    $wrapperEx->errorInfo = $ex->errorInfo;
                    throw $wrapperEx;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = 'id')
    {
        return (int)$this->getPdo()->lastInsertId($name);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->transactionCount = 0;
        $this->closeCachedStatements();
        $this->pdo = null;
    }

    /**
     * Close the connection before serializing.
     *
     * @return array
     */
    public function __sleep()
    {
        $this->close();

        return array_keys((array)$this);
    }

    /**
     * On clone object.
     */
    public function __clone()
    {
        throw new \LogicException('Not cloneable.');
    }

    /**
     * Private methods.
     */

    /**
     * Execute the statement.
     *
     * @param PdoQuery $query
     *
     * @return \PDOStatement
     */
    private function execute(PdoQuery $query)
    {
        $statement = $this->getStatement($query->getQueryStr());
        $this->bindValues($statement, $query->getParameters());

        try {
            if (!$statement->execute()) {
                $ex = new PdoWrapperException('Failed execute the statement.', $query);
                $ex->errorInfo = $statement->errorInfo();
                throw $ex;
            }
        } catch (\PDOException $ex) {
            if ($ex instanceof PdoWrapperException) {
                throw $ex;
            } else {
                $wrapperEx = new PdoWrapperException($ex->getMessage(), $query, $ex->getCode(), $ex);
                $wrapperEx->errorInfo = $statement->errorInfo();
                throw $wrapperEx;
            }
        }

        return $statement;
    }

    /**
     * Get a prepared statement.
     *
     * @param string $queryStr
     *
     * @return \PDOStatement
     */
    private function getStatement($queryStr)
    {
        $hash = crc32($queryStr);

        // cache prepared statement
        if (!array_key_exists($hash, $this->cachedStatements)) {
            $this->cachedStatements[$hash] = $this->getPdo()->prepare($queryStr);
        }

        return $this->cachedStatements[$hash];
    }

    /**
     * Bind values.
     *
     * @param $statement     \PDOStatement
     * @param PdoParameter[] $parameters
     *
     * @return $this
     */
    private function bindValues(\PDOStatement $statement, array $parameters)
    {
        foreach ($parameters as $parameter) {
            $statement->bindValue(
                $parameter->getName(),
                $parameter->getValue(),
                $parameter->getType()
            );
        }

        return $this;
    }

    /**
     * Close all cached prepared statements.
     */
    private function closeCachedStatements()
    {
        foreach ($this->cachedStatements as &$statement) {
            $statement = null;
        }
        unset($statement);
        $this->cachedStatements = [];
    }
}
