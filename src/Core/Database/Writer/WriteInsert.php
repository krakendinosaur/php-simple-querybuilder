<?php

namespace Core\Database\Writer;

use Core\Exception\QueryBuilderException;

class WriteInsert extends AbstractWriter implements WriterInterface
{
    public function write()
    {
        try {
            $values = $this->syntax->getValues();
            $syntax = $this->writeSyntax();
            $table = $this->writeTable();

            $fields = array();

            if (empty($values)) {
                throw new QueryBuilderException("Error: Missing values on " . $syntax . " syntax.");
                return null;
            } else {
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
            }
        } catch (QueryBuilderException $e) {
            $this->logger->write($e->getMessage());
        }
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
