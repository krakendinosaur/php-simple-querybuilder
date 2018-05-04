<?php

namespace Core\Database\Writer;

use Core\Database\Raw;
use Core\Exception\QueryBuilderException;

class WriteSelect extends AbstractWriter implements WriterInterface
{
    public function write()
    {
        $columns = $this->writeColumns();
        $table = $this->writeTable();
        $join = $this->writeJoin();
        $where = $this->writeWhere();
        $groupBy = $this->writeGroupBy();
        $having = $this->writeHaving();
        $orderBy = $this->writeOrderBy();
        $limit = $this->writeLimit();

        $this->statements = array(
            'SELECT ' . $columns,
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
            $allColumns = $this->wrapArray($columns);
        } else {
            $allColumns = "*";
        }

        return trim($allColumns);
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

    private function writeGroupBy()
    {
        $groupBy = $this->syntax->getGroupBy();
        $allGroupBy = "";

        if (is_array($groupBy) && !empty($groupBy)) {
            $allGroupBy .= "GROUP BY " . $this->wrapArray($groupBy);
        }

        return trim($allGroupBy);
    }

    private function writeHaving()
    {
        $having = $this->syntax->getHaving();

        $allHaving = "";
        if (is_array($having) && !empty($having)) {
            try {
                if (empty($this->syntax->getGroupBy())) {
                    throw new QueryBuilderException("Error: GROUP BY is required for HAVING clause");
                } else {
                    $allHaving .= "HAVING " . $this->writeExpressions($having);
                }
            } catch (QueryBuilderException $e) {
                $this->logger->write($e->getMessage());
            }
        }

        return trim($allHaving);
    }

    private function writeOrderBy()
    {
        $orderBy = $this->syntax->getOrderBy();
        $allOrderBy = "";

        if (is_array($orderBy) & !empty($orderBy)) {
            $allOrderBy = "ORDER BY ";
            $arrOrderBy = array();
            foreach ($orderBy as $col => $sort) {
                $arrOrderBy[] = $this->wrap($col) . " " . $sort;
            }

            $allOrderBy .= implode(",", $arrOrderBy);
        }

        return trim($allOrderBy);
    }
}
