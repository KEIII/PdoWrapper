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
    private $cache = [];

    /**
     * Constructor.
     *
     * @param string      $dsn
     * @param string|null $username
     * @param string|null $passwd
     * @param array       $options
     */
    public function __construct($dsn, $username = null, $passwd = null, array $options = null)
    {
        $_options = array_replace($this->getDefaultOptions(), $options ?: []);
        $this->pdo = new \PDO((string)$dsn, (string)$username, (string)$passwd, $_options);
    }

    /**
     * @return \PDO
     *
     * @throws \LogicException
     */
    public function getPdo()
    {
        if (!$this->pdo instanceof \PDO) {
            throw new \LogicException('Connection is closed.');
        }

        return $this->pdo;
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
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
            if (!$this->getPdo()->beginTransaction()) {
                throw new \PDOException('Initiates a transaction failure.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        $this->transactionCount = 0;
        $this->getPdo()->rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if ($this->transactionCount <= 0) {
            throw new \LogicException('No transaction found.');
        }

        if (--$this->transactionCount === 0) {
            if (!$this->getPdo()->commit()) {
                throw new PdoWrapperException('Commits a transaction failure.');
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
        $this->cache = [];
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
     * Execute statement.
     *
     * @param PdoQuery $query
     *
     * @return \PDOStatement
     */
    private function execute(PdoQuery $query)
    {
        $statement = $this->getStatement($query->getQueryStr());
        $this->bindValues($statement, $query->getParameters());

        if (!$statement->execute()) {
            throw new PdoWrapperException('Failed execute statement.');
        }

        return $statement;
    }

    /**
     * Get prepared statement.
     *
     * @param string $query
     *
     * @return \PDOStatement
     */
    private function getStatement($query)
    {
        $hash = crc32($query);

        // cache prepared statement
        if (!array_key_exists($hash, $this->cache)) {
            $this->cache[$hash] = $this->getPdo()->prepare($query);
        }

        return $this->cache[$hash];
    }

    /**
     * Binds values.
     *
     * @param $statement \PDOStatement
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
}
