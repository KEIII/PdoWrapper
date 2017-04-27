<?php

namespace KEIII\PdoWrapper\Tests;

use KEIII\PdoWrapper\PdoPostgres;

/**
 * Pdo Postgres Test.
 */
class PdoPostgresTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildQueryWithLimit()
    {
        self::assertSame(
            'SELECT COUNT(*) OVER() AS __count, * FROM people LIMIT :__offset, :__limit;',
            PdoPostgres::buildQueryStrWithLimit('SELECT * FROM people;')
        );

        self::assertSame(
            'SELECT COUNT(*) OVER() AS __count, * FROM people LIMIT :__offset, :__limit;',
            PdoPostgres::buildQueryStrWithLimit('select * FROM people;')
        );
    }
}
