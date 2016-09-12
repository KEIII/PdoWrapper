<?php

namespace KEIII\PdoWrapper\Tests;

use KEIII\PdoWrapper\PdoQuery;
use KEIII\PdoWrapper\PdoWrapper;

/**
 * Pdo Wrapper Test.
 */
class PdoWrapperTest extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var PdoWrapper|null
     */
    private $pdoWrapper;

    /**
     * {@inheritdoc}
     */
    protected function getConnection()
    {
        if (!$this->pdoWrapper) {
            $this->pdoWrapper = new PdoWrapper('sqlite::memory:');
            $this->pdoWrapper->write(new PdoQuery('
                CREATE TABLE IF NOT EXISTS people (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    first_name VARCHAR(255),
                    last_name VARCHAR(255)
                );
            '));
        }

        return $this->createDefaultDBConnection($this->pdoWrapper->getPdo(), ':memory:');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(__DIR__.'/fixtures/people.xml');
    }

    public function testRead()
    {
        $sql = 'SELECT * FROM people WHERE first_name = :first_name;';
        $query = new PdoQuery($sql, [':first_name' => 'Penelope']);
        $people = $this->pdoWrapper->read($query)->asArray();

        self::assertCount(2, $people);
    }

    public function testOne()
    {
        $sql = 'SELECT * FROM people WHERE first_name = :first_name;';
        $query = new PdoQuery($sql, [':first_name' => 'John']);
        $john = $this->pdoWrapper->read($query)->getFirst();

        self::assertSame('Smith', $john['last_name']);
    }

    public function testColumn()
    {
        $sql = 'SELECT * FROM people WHERE first_name = :first_name;';
        $query = new PdoQuery($sql, [':first_name' => 'Penelope']);
        $ids = $this->pdoWrapper->read($query)->asColumn();

        self::assertEquals([3, 5], $ids);
    }

    public function testWrite()
    {
        $this->pdoWrapper->beginTransaction();
        $this->pdoWrapper->write(new PdoQuery('INSERT INTO people (first_name, last_name) VALUES (:first_name, :last_name)', [
            ':first_name' => 'Mike',
            ':last_name' => 'Black',
        ]));
        $this->pdoWrapper->commit();

        $mike = $this->pdoWrapper->read(new PdoQuery('SELECT * FROM people WHERE id = :id;', [
            ':id' => $this->pdoWrapper->lastInsertId(),
        ]))->getFirst();

        self::assertEquals([
            'id' => 11,
            'first_name' => 'Mike',
            'last_name' => 'Black',
        ], $mike);
    }

    public function testRollBack()
    {
        $this->pdoWrapper->beginTransaction();
        $this->pdoWrapper->write(new PdoQuery('INSERT INTO people (first_name, last_name) VALUES (:first_name, :last_name)', [
            ':first_name' => 'Mike',
            ':last_name' => 'Black',
        ]));
        $this->pdoWrapper->rollBack();

        $mike = $this->pdoWrapper->read(new PdoQuery('SELECT * FROM people WHERE first_name = :first_name AND last_name = :last_name;', [
            ':first_name' => 'Mike',
            ':last_name' => 'Black',
        ]))->getFirst();

        self::assertFalse($mike);
    }

    public function testSleep()
    {
        self::assertNotEmpty(serialize(new PdoWrapper('sqlite::memory:')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParamName()
    {
        $sql = 'SELECT * FROM people WHERE id = :id;';
        $this->pdoWrapper->read(new PdoQuery($sql, ['id' => 1]))->getFirst();
    }

    public function testClose()
    {
        $pdo = new PdoWrapper('sqlite::memory:');
        $pdo->close();

        self::assertFalse($pdo->isConnected());
    }

    /**
     * @expectedException \LogicException
     */
    public function testClone()
    {
        $pdo = new PdoWrapper('sqlite::memory:');
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $pdo;
    }
}
