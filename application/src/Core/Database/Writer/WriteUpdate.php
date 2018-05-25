<?php

namespace Core\Database\Writer;

use Core\Exception\QueryBuilderException;

class WriteUpdate extends AbstractWriter implements WriterInterface
{
    public function write()
    {
        try {
            $values = $this->syntax->getValues();
            $table = $this->writeTable();
            $where = $this->writeWhere();
            $groupBy = $this->writeGroupBy();
            $having = $this->writeHaving();
            $orderBy = $this->writeOrderBy();
            $limit = $this->writeLimit();

            $fields = array();

            if (empty($values)) {
                throw new QueryBuilderException("Error: Missing values on UPDATE syntax.");
                return null;
            } else {
                foreach ($values as $key => $value) {
                    $fields[] = $this->wrapper . $key . $this->wrapper . " = " . $this->parameterize($value);
                }

                $fieldsvals = implode(",\n", $fields);

                if (empty($where)) {
                    throw new QueryBuilderException("Error: Missing WHERE clause in UPDATE syntax.");
                    return null;
                } else {
                    $this->statements = array(
                        'UPDATE',
                        $table,
                        'SET',
                        $fieldsvals,
                        $where,
                        $groupBy,
                        $having,
                        $orderBy,
                        $limit
                    );
                    
                    return $this->parseStatements();
                }
            }
        } catch (QueryBuilderException $e) {
            $this->logger->write($e->getMessage());
        }
    }
}
