<?php

namespace Core\Database\Writer;

use Core\Database\AbstractBaseQuery;
use Core\Database\Insert;
use Core\Database\Update;
use Core\Database\Select;
use Core\Database\Delete;

class WriterFactory
{
    public function getWriter(AbstractBaseQuery $class, WriteParameter $parameterWriter)
    {
        if ($class instanceof Insert) {
            return new WriteInsert($class, $parameterWriter);
        } elseif ($class instanceof Update) {
            return new WriteUpdate($class, $parameterWriter);
        } elseif ($class instanceof Select) {
            return new WriteSelect($class, $parameterWriter);
        } elseif ($class instanceof Delete) {
            return new WriteDelete($class, $parameterWriter);
        } else {
            return null;
        }
    }
}
