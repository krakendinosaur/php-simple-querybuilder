<?php
/**
 * A simple class for Insert syntax.
 *
 * PHP Version 5.6.35
 *
 * @author Prince Ryan Sy
 */

namespace Core\Database;

class Insert extends AbstractBaseQuery
{
    private $ignore = false;
    private $replace = false;

    public function ignore()
    {
        $this->ignore = true;

        return $this;
    }

    public function getIgnore()
    {
        return $this->ignore;
    }

    public function replace()
    {
        $this->replace = true;

        return $this;
    }

    public function getReplace()
    {
        return $this->replace;
    }
}
