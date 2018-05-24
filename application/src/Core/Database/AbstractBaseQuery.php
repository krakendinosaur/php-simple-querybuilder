<?php
/**
 * An abstract class for standard query syntax.
 *
 * PHP Version 5.6.35
 *
 * @author Prince Ryan Sy
 */

namespace Core\Database;

use Core\Database\Writer\WriterFactory;
use Core\Database\Writer\WriteParameter;

abstract class AbstractBaseQuery
{
    protected $db;
    protected $tables = array();
    protected $query;
    protected $parameters = array();
    protected $wf;
    protected $writer;
    protected $parameterWriter;
    protected $values = array();
    protected $where = array();
    protected $groupBy = array();
    protected $orderBy = array();
    protected $having = array();
    protected $limit;
    protected $nested = false;
    protected $nestedExpression = array();
    protected $schema;

    public function __construct(DB $db)
    {
        $this->db = $db;
        $this->wf = new WriterFactory;
        $this->parameterWriter = new WriteParameter;
        $this->schema = $this->db->getSchema();
        $this->writer = $this->wf->getWriter($this, $this->parameterWriter);
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function table($tables)
    {
        if (!is_array($tables)) {
            $tables = func_get_args();
        }

        $this->tables = array_merge($this->tables, $tables);

        return $this;
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function values(array $values)
    {
        $this->values = array_merge($this->values, $values);
        
        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->whereHandler($column, $operator, $value, 'AND');

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->whereHandler($column, $operator, $value, 'OR');

        return $this;
    }

    public function whereBetween($column, $arg1, $arg2)
    {
        $this->whereHandler($column, 'BETWEEN', [$arg1, $arg2], 'AND');

        return $this;
    }

    public function orWhereBetween($column, $arg1, $arg2)
    {
        $this->whereHandler($column, 'BETWEEN', [$arg1, $arg2], 'OR');

        return $this;
    }

    public function whereNotBetween($column, $arg1, $arg2)
    {
        $this->whereHandler($column, 'NOT BETWEEN', [$arg1, $arg2], 'AND');

        return $this;
    }

    public function orWhereNotBetween($column, $arg1, $arg2)
    {
        $this->whereHandler($column, 'NOT BETWEEN', [$arg1, $arg2], 'OR');

        return $this;
    }

    public function whereIn($column, array $values)
    {
        $this->whereHandler($column, 'IN', $values, 'AND');

        return $this;
    }

    public function orWhereIn($column, array $values)
    {
        $this->whereHandler($column, 'IN', $values, 'OR');

        return $this;
    }

    public function whereNotIn($column, array $values)
    {
        $this->whereHandler($column, 'NOT IN', $values, 'AND');

        return $this;
    }

    public function orWhereNotIn($column, array $values)
    {
        $this->whereHandler($column, 'NOT IN', $values, 'OR');

        return $this;
    }

    public function whereNull($column)
    {
        $this->whereHandler($column, 'IS', DB::raw('NULL'), 'AND');

        return $this;
    }

    public function orWhereNull($column)
    {
        $this->whereHandler($column, 'IS', DB::raw('NULL'), 'OR');

        return $this;
    }

    public function whereNotNull($column)
    {
        $this->whereHandler($column, 'IS NOT', DB::raw('NULL'), 'AND');

        return $this;
    }

    public function orWhereNotNull($column)
    {
        $this->whereHandler($column, 'IS NOT', DB::raw('NULL'), 'OR');

        return $this;
    }

    public function whereExists($table, $column, \Closure $value)
    {
        $operator = 'EXISTS';

        $this->whereExistsHandler($table, $column, $operator, $value, 'AND');

        return $this;
    }

    public function orWhereExists($table, $column, \Closure $value)
    {
        $operator = 'EXISTS';

        $this->whereExistsHandler($table, $column, $operator, $value, 'OR');

        return $this;
    }

    public function whereNotExists($table, $column, \Closure $value)
    {
        $operator = 'NOT EXISTS';

        $this->whereExistsHandler($table, $column, $operator, $value, 'AND');

        return $this;
    }

    public function orWhereNotExists($table, $column, \Closure $value)
    {
        $operator = 'NOT EXISTS';

        $this->whereExistsHandler($table, $column, $operator, $value, 'OR');

        return $this;
    }

    protected function whereExistsHandler($table, $column, $operator, $value, $link)
    {
        if ($this->nested) { // for where exists and nested expressions
            $this->nestedExpression[] = compact('table', 'column', 'operator', 'value', 'link');
        } else {
            $this->where[] = compact('table', 'column', 'operator', 'value', 'link');
        }
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function setNested($nested)
    {
        if (is_bool($nested)) {
            $this->nestedExpression = array();
            $this->nested = $nested;
        }
        
        return $this;
    }

    public function getNestedExpression()
    {
        return $this->nestedExpression;
    }

    protected function whereHandler($column, $operator, $value, $link)
    {
        if ($this->nested) { // for where exists and nested expressions
            $this->nestedExpression[] = compact('column', 'operator', 'value', 'link');
        } else {
            $this->where[] = compact('column', 'operator', 'value', 'link');
        }
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

    public function limit($offset, $rowCount = null)
    {
        if (!empty($rowCount)) {
            $this->limit = array($offset, $rowCount);
        } else {
            $this->limit = $offset;
        }

        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function exec()
    {
        $this->writer->setParameterize();

        $this->writeQuery();

        $this->writeParameters();

        $result = $this->db->query(preg_replace("/[\n]/", " ", $this->query), $this->parameters);

        return $result;
    }

    public function debug()
    {
        $this->writer->setParameterize();

        $this->writeQuery();

        $this->writeParameters();

        echo "<pre>";
        if (!empty($this->parameters)) {
            print_r($this->parameters);
            echo "<br/>";
        }
        echo $this->query;
        echo "</pre>";

        return $this;
    }

    public function getQuery()
    {
        $this->writer->setParameterize(false);

        $this->writeQuery();

        return $this->query;
    }

    protected function writeQuery()
    {
        $this->parameterWriter->reset();

        $this->query = $this->writer->write();

        return $this;
    }

    protected function writeParameters()
    {
        $this->parameters = array_merge($this->parameters, $this->parameterWriter->get());

        return $this;
    }
}
