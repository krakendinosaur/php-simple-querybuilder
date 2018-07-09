<?php
/**
 * A simple class for Select syntax.
 *
 * PHP Version 5.6.35
 *
 * @author Prince Ryan Sy
 */

namespace Core\Database;

class Select extends AbstractBaseQuery
{
    private $columns = array();
    private $join = array();
    private $count = false;

    public function columns($columns)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        
        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    public function count()
    {
        $this->count = true;
        return $this->exec()[0]['count'];
    }

    public function one()
    {
        return $this->limit(1)->exec()[0];
    }

    public function name()
    {
        return $this
        ->columns("name")
        ->limit(1)
        ->exec()[0]['name'];
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function fullJoin($table, $column = null, $operator = null, $value = null)
    {
        $type = 'FULL OUTER';

        $this->joinHandler($type, $table, $column, $operator, $value);

        return $this;
    }

    public function innerJoin($table, $column = null, $operator = null, $value = null, $outer = false)
    {
        $type = 'INNER';

        $this->joinHandler($type, $table, $column, $operator, $value);

        return $this;
    }

    public function leftJoin($table, $column = null, $operator = null, $value = null, $outer = false)
    {
        $type = 'LEFT';

        if ($outer) {
            $type .= ' OUTER';
        }

        $this->joinHandler($type, $table, $column, $operator, $value);

        return $this;
    }

    public function rightJoin($table, $column = null, $operator = null, $value = null, $outer = false)
    {
        $type = 'RIGHT';

        if ($outer) {
            $type .= ' OUTER';
        }

        $this->joinHandler($type, $table, $column, $operator, $value);

        return $this;
    }

    private function joinHandler($type, $table, $column, $operator, $value)
    {
        $onClause = array();

        $link = 'AND';

        $onClause[] = compact('column', 'operator', 'value', 'link');

        $this->join[] = compact('type', 'table', 'onClause');
    }

    public function getJoin()
    {
        return $this->join;
    }
}
