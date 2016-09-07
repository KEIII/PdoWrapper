<?php

namespace KEIII\PdoWrapper;

class PdoParameter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var int
     */
    private $type;

    /**
     * @var array
     */
    protected static $pdoTypes = [
        'string' => \PDO::PARAM_STR,
        'integer' => \PDO::PARAM_INT,
        'boolean' => \PDO::PARAM_BOOL,
        'NULL' => \PDO::PARAM_NULL,
        'resource' => \PDO::PARAM_LOB,
    ];

    /**
     * Constructor.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __construct($name, $value)
    {
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    private function setName($name)
    {
        $name = (string)$name;

        if (0 !== strpos($name, ':')) {
            throw new \InvalidArgumentException('Parameter name must start from ":" symbol.');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    private function setValue($value)
    {
        $this->value = $value;
        $this->updateType();

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    private function updateType()
    {
        $defaultType = self::$pdoTypes['string'];
        $valueType = gettype($this->value);
        $this->type = array_key_exists($valueType, self::$pdoTypes) ? self::$pdoTypes[$valueType] : $defaultType;

        return $this;
    }
}
