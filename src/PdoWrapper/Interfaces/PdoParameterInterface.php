<?php

namespace KEIII\PdoWrapper\Interfaces;

interface PdoParameterInterface
{
    /**
     * Get a parameter name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get a parameter value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Get a parameter type.
     *
     * @return int
     */
    public function getType();
}
