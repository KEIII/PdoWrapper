<?php

namespace KEIII\PdoWrapper;

class PdoDataReader implements PdoDataReaderInterface
{
    /**
     * @var \PDOStatement
     */
    private $statement;

    /**
     * Constructor.
     *
     * @param \PDOStatement $statement
     */
    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * {@inheritdoc}
     */
    public function asGenerator()
    {
        while ($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }

        $this->statement->closeCursor();
    }

    /**
     * {@inheritdoc}
     */
    public function asArray()
    {
        return iterator_to_array($this->asGenerator());
    }

    /**
     * {@inheritdoc}
     */
    public function getFirst()
    {
        foreach ($this->asGenerator() as $row) {
            $this->statement->closeCursor();

            return $row;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function asColumn($column = 'id')
    {
        $result = $this->statement->fetchAll(\PDO::FETCH_COLUMN, (string)$column);
        $this->statement->closeCursor();

        return $result;
    }
}
