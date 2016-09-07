<?php

namespace KEIII\PdoWrapper\Tests;

use KEIII\PdoWrapper\PdoMysql;

/**
 * Pdo Mysql Test.
 */
class PdoMysqlTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildQueryWithLimit()
    {
        self::assertSame(
            'SELECT SQL_CALC_FOUND_ROWS * FROM PEOPLE LIMIT :__offset, :__limit;',
            PdoMysql::buildQueryStrWithLimit('SELECT * FROM PEOPLE;')
        );

        self::assertSame(
            'SELECT SQL_CALC_FOUND_ROWS * FROM PEOPLE LIMIT :__offset, :__limit;',
            PdoMysql::buildQueryStrWithLimit('select * FROM PEOPLE;')
        );
    }
}
