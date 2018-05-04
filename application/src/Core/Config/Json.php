<?php

namespace Core\Config;

class Json extends FileAbstract
{
    private $contents;

    public function parse()
    {
        $this->contents = file_get_contents($this->path);
        return json_decode($this->contents, true);
    }
}
