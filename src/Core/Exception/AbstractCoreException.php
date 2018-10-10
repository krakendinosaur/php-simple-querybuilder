<?php

namespace Core\Exception;

abstract class AbstractCoreException extends \Exception
{
    public function getFullMessage()
    {
        $msg = substr(strrchr(get_class($this), "\\"), 1) . ": " . $this->getMessage();
        if ($this->getLine()) {
            $msg .= " at line " . $this->getLine() . " ";
        }
        if ($_SERVER['SCRIPT_NAME']) {
            $msg .= "on file " .  $_SERVER['SCRIPT_NAME'];
        }
        return $msg;
    }
}
