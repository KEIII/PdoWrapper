<?php

namespace KEIII\PdoWrapper\tests;

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
            PdoMysql::buildQueryWithLimit('SELECT * FROM PEOPLE;')
        );
    }
}
