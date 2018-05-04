<?php

namespace Core\Config;

class Php extends FileAbstract
{
    public function parse()
    {
        return include_once($this->path);
    }
}
