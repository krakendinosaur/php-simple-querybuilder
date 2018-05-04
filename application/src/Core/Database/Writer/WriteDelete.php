<?php

namespace Core\Database\Writer;

use Core\Exception\QueryBuilderException;

class WriteDelete extends AbstractWriter implements WriterInterface
{
    public function write()
    {
        try {
            $table = $this->writeTable();
            $where = $this->writeWhere();
            $limit = $this->writeLimit();

            if (empty($where)) {
                throw new QueryBuilderException("Error: Where clause is required in DELETE syntax.");
            } else {
                $this->statements = array(
                    'DELETE FROM',
                    $table,
                    $where,
                    $limit
                );
                
                return $this->parseStatements();
            }
        } catch (QueryBuilderException $e) {
            $this->logger->write($e->getMessage());
        }
    }
}
