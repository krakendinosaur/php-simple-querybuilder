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
    private $groupBy = array();
    private $orderBy = array();
    private $having = array();

    public function columns($columns)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        
        $this->columns = array_merge($this->columns, $columns);

        return $this;
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

    public function groupBy($groupBy)
    {
        if (!is_array($groupBy)) {
            $groupBy = func_get_args();
        }
        
        $this->groupBy = array_merge($this->groupBy, $groupBy);
        
        return $this;
    }

    public function getGroupBy()
    {
        return $this->groupBy;
    }

    public function having($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $link = "AND";

        $this->havingHandler($column, $operator, $value, $link);

        return $this;
    }

    public function orHaving($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $link = "OR";

        $this->havingHandler($column, $operator, $value, $link);

        return $this;
    }

    private function havingHandler($column, $operator, $value, $link)
    {
        if ($this->nested) {
            $this->nestedExpression[] = compact('column', 'operator', 'value', 'link');
        } else {
            $this->having[] = compact('column', 'operator', 'value', 'link');
        }
    }

    public function getHaving()
    {
        return $this->having;
    }

    public function orderBy()
    {
        if (func_num_args() === 1) {
            if (is_array(func_get_arg(0))) {
                $this->orderBy = array_merge($this->orderBy, func_get_arg(0));
            } else {
                $this->orderBy = array_merge($this->orderBy, [func_get_arg(0) => 'ASC']);
            }
        } elseif (func_num_args() === 2) {
            $this->orderBy = array_merge($this->orderBy, [func_get_arg(0) => func_get_arg(1)]);
        }
        
        return $this;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }
}
