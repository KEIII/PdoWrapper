<?php

namespace KEIII\PdoWrapper;

use KEIII\PdoWrapper\Interfaces\PdoPaginatableInterface;
use KEIII\PdoWrapper\Interfaces\PdoQueryInterface;

class PdoPostgres extends PdoWrapper implements PdoPaginatableInterface
{
    /**
     * {@inheritdoc}
     */
    public function paginatedResult(PdoQueryInterface $query, $limit = 10, $offset = 0)
    {
        $paginatedQueryStr = self::buildQueryStrWithLimit($query->getQueryStr());
        $parameters = array_reduce($query->getParameters(), function (array $carry, PdoParameter $parameter) {
            $carry[$parameter->getName()] = $parameter->getValue();

            return $carry;
        }, []);
        $paginatedParameters = array_replace($parameters, [
            ':__limit' => (int)$limit + 1, // add one additional limit to detect "has more"
            ':__offset' => (int)$offset,
        ]);
        $paginatedQuery = new PdoQuery($paginatedQueryStr, $paginatedParameters);

        $itemRows = $this->read($paginatedQuery)->asArray();
        $totalCount = count($itemRows) > 0 ? (int)$itemRows[0]['__count'] : 0;
        $hasMore = count($itemRows) > (int)$limit;

        if ($hasMore) {
            // remove additional last row
            array_pop($itemRows);
        }

        return new PdoPaginatedResult($itemRows, $totalCount, $hasMore);
    }

    /**
     * Prepare query string for pagination.
     *
     * @param string $queryStr The query string without limit and offset
     *
     * @return string The query string with limit and offset
     */
    public static function buildQueryStrWithLimit($queryStr)
    {
        $queryStr = rtrim((string)$queryStr, ';'); // remove last semicolon char
        $queryStr = self::replaceFirst('SELECT', 'SELECT COUNT(*) OVER() AS __count,', $queryStr, true);
        $queryStr .= ' LIMIT :__offset, :__limit;';

        return $queryStr;
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
