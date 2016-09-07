<?php

namespace KEIII\PdoWrapper;

class PdoQuery
{
    /**
     * Query string.
     *
     * @var string
     */
    private $queryStr = '';

    /**
     * Binded params.
     *
     * @var PdoParameter[]
     */
    private $parameters = [];

    /**
     * Constructor.
     *
     * @param string              $queryStr
     * @param PdoParameter[]|null $parameters
     */
    public function __construct($queryStr, array $parameters = null)
    {
        $this->setQueryStr($queryStr);
        $this->setParameters($parameters ?: []);
    }

    /**
     * @return string
     */
    public function getQueryStr()
    {
        return (string)$this->queryStr;
    }

    /**
     * @param string $queryStr
     *
     * @return $this
     */
    private function setQueryStr($queryStr)
    {
        $this->queryStr = (string)$queryStr;

        return $this;
    }

    /**
     * @return PdoParameter[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param PdoParameter[] $parameters
     *
     * @return $this
     */
    private function setParameters(array $parameters)
    {
        $this->parameters = [];

        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    private function setParameter($name, $value)
    {
        $parameter = new PdoParameter($name, $value);
        $this->parameters[$parameter->getName()] = $parameter;

        return $this;
    }
}
