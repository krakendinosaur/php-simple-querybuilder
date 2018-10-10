<?php

namespace Core\Database\Writer;

use Core\Database\Raw;
use Core\Exception\QueryBuilderException;

class WriteSelect extends AbstractWriter implements WriterInterface
{
    public function write()
    {
        $columns = $this->writeColumns();
        $countColumn = $this->writeCountColumn();
        $table = $this->writeTable();
        $join = $this->writeJoin();
        $where = $this->writeWhere();
        $groupBy = $this->writeGroupBy();
        $having = $this->writeHaving();
        $orderBy = $this->writeOrderBy();
        $limit = $this->writeLimit();

        $count = $this->syntax->getCount();

        $selectLine = 'SELECT ' . $columns;

        if ($count === true) {
            $selectLine = 'SELECT COUNT(*) AS `count`';

            if (!empty($countColumn)) {
                $selectLine = 'SELECT COUNT(' . $countColumn . ') AS `count`';
            }
        }

        $this->statements = array(
            $selectLine,
            'FROM',
            $table,
            $join,
            $where,
            $groupBy,
            $having,
            $orderBy,
            $limit
        );

        return $this->parseStatements();
    }

    private function writeColumns()
    {
        $columns = $this->syntax->getColumns();
        $allColumns = "";

        if (is_array($columns) && !empty($columns)) {
            $allColumns = implode(",", $this->wrapArray($columns));
        } else {
            $allColumns = "*";
        }

        return trim($allColumns);
    }

    private function writeCountColumn()
    {
        $column = $this->syntax->getCountColumn();
        return (!empty($column))
            ? $this->wrap($column)
            : null;
    }

    private function writeJoin()
    {
        $join = $this->syntax->getJoin();
        $allJoin = "";

        if (is_array($join) && !empty($join)) {
            $joinCount = count($join);
            foreach ($join as $value) {
                $allJoin .= $value['type'] . " JOIN ";
                $allJoin .= $this->wrapTableSchema($value['table']) . "\n";
                $allJoin .= "ON " . $this->writeExpressions($value['onClause']);
                $allJoin .= "\n";
            }
        }

        return trim($allJoin);
    }
}
