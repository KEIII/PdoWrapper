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
        $paginatedSql = self::buildQueryWithLimit($query->getQueryStr());
        $paginatedParameters = array_replace($query->getParameters(), [
            ':__limit' => (int)$limit + 1, // add one additional limit to detect "has more"
            ':__offset' => (int)$offset,
        ]);
        $paginatedQuery = new PdoQuery($paginatedSql, $paginatedParameters);

        $itemRows = $this->read($paginatedQuery)->asArray();
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
        $query = self::replaceFirst('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $query, true);
        $query .= ' LIMIT :__offset, :__limit;';

        return $query;
    }

    /**
     * Replace only acts on the first match.
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param bool   $caseInsensitive
     *
     * @return string
     */
    protected static function replaceFirst($from, $to, $subject, $caseInsensitive = false)
    {
        $pos = $caseInsensitive ? stripos($subject, $from) : strpos($subject, $from);

        if ($pos !== false) {
            return substr_replace($subject, $to, $pos, strlen($from));
        }

        return $subject;
    }
}
