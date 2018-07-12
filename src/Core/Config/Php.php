<?php

namespace Core\Config;

class Php extends FileAbstract
{
    public function parse()
    {
        return include($this->path);
    }
}
