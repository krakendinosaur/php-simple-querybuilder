<?php

namespace Core\Exception;

abstract class AbstractCoreException extends \Exception
{
    public function getFullMessage()
    {
        $msg = get_class($this) . ": " . $this->getMessage();
        if ($this->getLine()) {
            $msg .= " at line " . $this->getLine() . " ";
        }
        if ($this->getFile()) {
            $msg .= "on file " .  $this->getFile();
        }
        return $msg;
    }
}
