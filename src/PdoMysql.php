<?php

namespace KEIII\PdoWrapper;

class PdoMysql extends PdoWrapper implements PdoPaginatableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions()
    {
        return array_replace(parent::getDefaultOptions(), [
            \PDO::ATTR_EMULATE_PREPARES => true, // Emulate prepare. For MySQL this need to be set true so that PDO can emulate the prepare support to bypass the buggy native prepare support.
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function paginatedResult(PdoQuery $query, $limit = 10, $offset = 0)
    {
        $_query = clone $query;
        // add one additional limit to detect "has more"
        $_query->setParameter(':__limit', (int)$limit + 1);
        $_query->setParameter(':__offset', (int)$offset);
        $_query->setQueryStr(self::buildQueryWithLimit($query->getQueryStr()));

        $itemRows = $this->read($_query)->asArray();
        $countRow = $this->read(new PdoQuery('SELECT FOUND_ROWS() as __count'))->getFirst();
        $totalCount = $countRow['__count'];
        $hasMore = count($itemRows) > (int)$limit;

        if ($hasMore) {
            // remove additional last row
            array_pop($itemRows);
        }

        return new PdoPaginatedResult($itemRows, $totalCount, $hasMore);
    }

    /**
     * Prepare sql query for pagination.
     *
     * @param string $query The sql query without limit and offset
     *
     * @return string The sql query with limit and offset
     */
    public static function buildQueryWithLimit($query)
    {
        $query = rtrim((string)$query, ';'); // remove last semicolon char
        $query = self::replaceFirst('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $query);
        $query .= ' LIMIT :__offset, :__limit;';

        return $query;
    }

    /**
     * Replace only acts on the first match.
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     *
     * @return string
     */
    protected static function replaceFirst($from, $to, $subject)
    {
        $pos = strpos($subject, $from);

        if ($pos !== false) {
            return substr_replace($subject, $to, $pos, strlen($from));
        }

        return $subject;
    }
}
