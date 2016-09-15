<?php

namespace KEIII\PdoWrapper\Exceptions;

use KEIII\PdoWrapper\Interfaces\PdoQueryInterface;

/**
 * Represents an error raised by PdoWrapper.
 */
class PdoWrapperException extends \PDOException
{
    /**
     * @var PdoQueryInterface|null
     */
    private $query;

    /**
     * Constructor.
     *
     * @param string                 $message
     * @param PdoQueryInterface|null $query
     * @param string|null            $code
     * @param \Exception|null        $previous
     */
    public function __construct($message, PdoQueryInterface $query = null, $code = null, \Exception $previous = null)
    {
        parent::__construct((string)$message, (int)$code, $previous);

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
        return $this->query instanceof PdoQueryInterface;
    }

    /**
     * @return PdoQueryInterface
     */
    public function getQuery()
    {
        if (!$this->hasQuery()) {
            throw new \LogicException('Query not found. Use hasQuery method before.');
        }

        return $this->query;
    }
}
