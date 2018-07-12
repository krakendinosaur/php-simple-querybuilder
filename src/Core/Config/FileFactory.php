<?php

namespace Core\Config;

class FileFactory
{
    private $path;

    public function __construct($path = null)
    {
        $this->path = $path;
    }

    public function php($path = null)
    {
        if (is_null($path)) {
            $path = $this->path;
        }
        return new Php($path);
    }

    public function json($path = null)
    {
        if (is_null($path)) {
            $path = $this->path;
        }
        return new Json($path);
    }
}
