<?php

namespace KEIII\PdoWrapper;

use KEIII\PdoWrapper\Exceptions\PdoWrapperException;
use KEIII\PdoWrapper\Interfaces\PdoParameterInterface;
use KEIII\PdoWrapper\Interfaces\PdoQueryInterface;
use KEIII\PdoWrapper\Interfaces\PdoWrapperInterface;

class PdoWrapper implements PdoWrapperInterface
{
    /**
     * @var \PDO|null
     */
    private $pdo;

    /**
     * @var int
     */
    private $transactionLevel = 0;

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
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if ($this->isConnected()) {
            throw new PdoWrapperException('Previous connection is not closed.');
        }

        try {
            $this->pdo = new \PDO(
                $this->dsn,
                null !== $this->username ? (string)$this->username : null,
                null !== $this->password ? (string)$this->password : null,
                array_replace(static::getDefaultOptions(), $this->options)
            );
        } catch (\PDOException $ex) {
            throw $this->wrapPdoException($ex);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reconnect()
    {
        $this->close();
        $this->connect();
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return $this->pdo instanceof \PDO;
    }

    /**
     * {@inheritdoc}
     */
    public function getPdo()
    {
        if (!$this->isConnected()) {
            $this->connect();
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
    public function read(PdoQueryInterface $query)
    {
        return new PdoDataReader($this->execute($query));
    }

    /**
     * {@inheritdoc}
     */
    public function write(PdoQueryInterface $query)
    {
        $statement = $this->execute($query);
        $statement->closeCursor();
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        if (++$this->transactionLevel === 1) {
            $pdo = $this->getPdo();

            try {
                if (!$pdo->beginTransaction()) {
                    $ex = new PdoWrapperException('Initiates a transaction failure.');
                    $ex->errorInfo = $pdo->errorInfo();
                    throw $ex;
                }
            } catch (\PDOException $ex) {
                $this->close();
                throw $this->wrapPdoException($ex);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        if ($this->transactionLevel > 0
            && --$this->transactionLevel === 0
        ) {
            $pdo = $this->getPdo();

            try {
                if (!$pdo->rollBack()) {
                    $ex = new PdoWrapperException('Rollback a transaction failure.');
                    $ex->errorInfo = $pdo->errorInfo();
                    throw $ex;
                }
            } catch (\PDOException $ex) {
                $this->close();
                throw $this->wrapPdoException($ex);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if ($this->transactionLevel <= 0) {
            throw new PdoWrapperException('No transaction found.');
        }

        if (--$this->transactionLevel === 0) {
            $pdo = $this->getPdo();

            try {
                if (!$pdo->commit()) {
                    $ex = new PdoWrapperException('Commits a transaction failure.');
                    $ex->errorInfo = $pdo->errorInfo();
                    throw $ex;
                }
            } catch (\PDOException $ex) {
                $this->close();
                throw $this->wrapPdoException($ex);
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
        $this->transactionLevel = 0;
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
     * On destruct object.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Private methods.
     */

    /**
     * Wrap the PDO exception.
     *
     * @param \PDOException $ex
     *
     * @return PdoWrapperException
     */
    private function wrapPdoException(\PDOException $ex)
    {
        if (!$ex instanceof PdoWrapperException) {
            $_ex = new PdoWrapperException($ex->getMessage(), null, $ex->getCode(), $ex);
            $_ex->errorInfo = $ex->errorInfo;
            $ex = $_ex;
        }

        return $ex;
    }

    /**
     * Execute the statement.
     *
     * @param PdoQueryInterface $query
     *
     * @return \PDOStatement
     */
    private function execute(PdoQueryInterface $query)
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
            $this->close();
            throw $this->wrapPdoException($ex);
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
     * @param \PDOStatement           $statement
     * @param PdoParameterInterface[] $parameters
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
