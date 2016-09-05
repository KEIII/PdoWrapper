<?php

namespace KEIII\PdoWrapper;

/**
 * Represents an error raised by PdoWrapper.
 */
class PdoWrapperException extends \PDOException
{
    /**
     * @var PdoQuery|null
     */
    private $query;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param PdoQuery|null   $query
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($message, PdoQuery $query = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->query = $query;
    }

    /**
     * @return array
     */
    public function getErrorInfo()
    {
        return is_array($this->errorInfo) ? $this->errorInfo : [];
    }

    /**
     * @return bool
     */
    public function hasQuery()
    {
        return $this->query instanceof PdoQuery;
    }

    /**
     * @return PdoQuery
     */
    public function getQuery()
    {
        if (!$this->hasQuery()) {
            throw new \LogicException('Query not found. Use hasQuery method before.');
        }

        return $this->query;
    }
}
