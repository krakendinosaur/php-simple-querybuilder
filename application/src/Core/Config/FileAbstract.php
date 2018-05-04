<?php

namespace Core\Config;

abstract class FileAbstract
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    abstract public function parse();
}
