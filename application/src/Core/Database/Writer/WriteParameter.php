<?php

namespace Core\Database\Writer;

use Core\Database\Raw;

class WriteParameter implements WriterInterface
{
    private $values;
    private $count = 1;
    private $parameters = array();

    public function get()
    {
        return $this->parameters;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function reset()
    {
        $this->count = 1;
        $this->parameters = array();

        return $this;
    }

    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    public function write()
    {
        foreach ($this->values as $key => $value) {
            $this->add($value);
        }
    }

    public function add($value)
    {
        if (!$value instanceof Raw) {
            $paramKey = "v" . $this->count;
            $this->parameters[$paramKey] = $value;

            $this->count++;

            return $this;
        }
    }

    public function merge($parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        
        return $this;
    }
}
