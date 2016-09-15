<?php

namespace KEIII\PdoWrapper\Interfaces;

interface PdoQueryInterface
{
    /**
     * Get a query string.
     *
     * @return string
     */
    public function getQueryStr();

    /**
     * Get array of parameters.
     *
     * @return PdoParameterInterface[]
     */
    public function getParameters();
}
