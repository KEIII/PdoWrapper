<?php

namespace KEIII\PdoWrapper;

use KEIII\PdoWrapper\Interfaces\PdoParameterInterface;
use KEIII\PdoWrapper\Interfaces\PdoQueryInterface;

class PdoQuery implements PdoQueryInterface
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
     * @var PdoParameterInterface[]
     */
    private $parameters = [];

    /**
     * Constructor.
     *
     * @param string                       $queryStr
     * @param PdoParameterInterface[]|null $parameters
     */
    public function __construct($queryStr, array $parameters = null)
    {
        $this->setQueryStr($queryStr);
        $this->setParameters($parameters ?: []);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param PdoParameterInterface[] $parameters
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
