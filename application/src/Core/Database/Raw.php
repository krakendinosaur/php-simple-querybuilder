<?php
/**
 * A class for raw query expressions.
 *
 * PHP Version 5.6.35
 *
 * @author Prince Ryan Sy
 */

namespace Core\Database;

class Raw
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var array
     */
    protected $parameters = array();

    public function __construct($value, $parameters = array())
    {
        $this->value = $value;
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}
