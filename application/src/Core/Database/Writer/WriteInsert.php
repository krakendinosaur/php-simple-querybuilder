<?php

namespace Core\Database\Writer;

use Core\Database\Raw;

class WriteInsert extends AbstractWriter implements WriterInterface
{
    public function write()
    {
        $values = $this->syntax->getValues();
        $syntax = $this->writeSyntax();
        $table = $this->writeTable();

        $fields = array();

        foreach ($values as $key => $value) {
            $fields[] = $this->wrapper . $key . $this->wrapper . " = " . $this->parameterize($value);
        }

        $fieldsvals = implode(",\n", $fields);

        $this->statements = array(
            $syntax . " INTO",
            $table,
            'SET',
            $fieldsvals
        );

        return $this->parseStatements();
    } // write()

    private function writeSyntax()
    {
        $syntax = "";
        if ($this->syntax->getIgnore() === true) {
            $syntax = "INSERT IGNORE";
        } elseif ($this->syntax->getReplace() === true) {
            $syntax = "REPLACE";
        } else {
            $syntax = "INSERT";
        }

        return $syntax;
    }
}
