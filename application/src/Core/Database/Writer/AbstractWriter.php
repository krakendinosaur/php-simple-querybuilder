<?php

namespace Core\Database\Writer;

use Core\Logger;
use Core\Database\AbstractBaseQuery;
use Core\Database\Raw;
use Core\Exception\QueryBuilderException;

abstract class AbstractWriter
{
    protected $syntax;
    protected $parameterWriter;
    protected $wrapper = "`";
    protected $logger;
    protected $schema;
    protected $statements;
    protected $parameterize = true;

    public function __construct(AbstractBaseQuery $syntax, WriteParameter $parameterWriter)
    {
        $this->syntax = $syntax;
        $this->parameterWriter = $parameterWriter;
        $this->logger = new Logger(LOGPATH . 'querybuilder');
        $this->schema = $this->syntax->getSchema();
    }

    public function setParameterize($parameterize = true)
    {
        $this->parameterize = $parameterize;

        return $this;
    }

    protected function writeTable()
    {
        $tables = $this->syntax->getTables();

        return $this->wrapTableSchema($tables);
    }

    protected function writeWhere()
    {
        $where = $this->syntax->getWhere();
        $allWhere = "";

        if (is_array($where) && !empty($where)) {
            $allWhere = "WHERE " . $this->writeExpressions($where);
        }

        return $allWhere;
    } // writeWhere()

    protected function writeGroupBy()
    {
        $groupBy = $this->syntax->getGroupBy();
        $allGroupBy = "";

        if (is_array($groupBy) && !empty($groupBy)) {
            $allGroupBy .= "GROUP BY " . implode(",", $this->wrapArray($groupBy));
        }

        return trim($allGroupBy);
    }

    protected function writeExpressions($expressions) // TO DO: optimize this method.
    {
        $allExpression = "";

        foreach ($expressions as $expression) {
            $column = $expression['column'];
            $operator = $expression['operator'];
            $link = $expression['link'];
            $value = $expression['value'];

            if (is_null($value) && $column instanceof \Closure) { // nested expression
                $allExpression .= $link;

                $this->syntax->setNested(true);
                $column($this->syntax);
                $allExpression .= " (\n" . $this->writeExpressions($this->syntax->getNestedExpression()) . "\n)";
                $this->syntax->setNested(false);
            } elseif (isset($expression['table']) && !is_null($expression['table'])) { // handle where exists
                $table = $this->wrapTableSchema($expression['table']);
                $allExpression .= $link;
                $allExpression .= " " . $operator;
                $allExpression .= "\n(\nSELECT ";
                $allExpression .= $this->wrap($column) . " FROM\n";
                $allExpression .= $table;
                $allExpression .= "\nWHERE\n";
                $this->syntax->setNested(true);
                $value($this->syntax);
                $allExpression .= $this->writeExpressions($this->syntax->getNestedExpression()) . "\n)";
                $this->syntax->setNested(false);
            } elseif (is_array($value)) { // handle BETWEEN and IN operators
                $allExpression .= $link;
                $allExpression .= " " . $this->wrap($column);
                $allExpression .= " " . $operator;

                if ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') { // BETWEEN operators
                    $allExpression .= " " . $this->parameterize($value[0]);
                    $allExpression .= " AND " . $this->parameterize($value[1]);
                } else { // IN operator
                    $values = array();
                    foreach ($value as $val) {
                        $values[] = $this->parameterize($val);
                    }

                    $allExpression .= " (" . implode(',', $values) . ")";
                }
            } else { // standard where clause
                $allExpression .= $link;
                $allExpression .= " " . $this->wrap($column);
                $allExpression .= " " . $operator;
                $allExpression .= " " . $this->parameterize($value);
            }
            
            $allExpression .= "\n";
        } // loop through expressions

        return preg_replace('/^(\s?AND ?|\s?OR ?)|\s$/i', '', $allExpression);
    } // writeExpressions()

    protected function writeLimit()
    {
        $limit = $this->syntax->getLimit();
        $allLimit = "";

        if (!empty($limit)) {
            $allLimit = "LIMIT ";
            if (is_array($limit)) {
                $allLimit .= implode(",", $limit);
            } else {
                $allLimit .= $limit;
            }
        }

        return trim($allLimit);
    }

    protected function parameterize($value)
    {
        $paramValue = "";
        if ($value instanceof Raw) {
            $paramValue = $value;

            if ($value->getParameters()) {
                $this->parameterWriter->merge($value->getParameters());
            }
        } else {
            if ($this->parameterize) {
                $paramValue = ":v" . $this->parameterWriter->getCount();
                $this->parameterWriter->add($value);
            } else {
                $value = (is_string($value)) ? "'" . $value . "'" : $value;
                $paramValue = $value;
            }
        }

        return $paramValue;
    }

    protected function wrapTableSchema($tables)
    {
        if (is_array($tables)) {
            $tables = $this->wrapArray($tables);
            foreach ($tables as $table) {
                $tmpTables[] = $this->addTableSchema($table);
            } // loop through tables
            return implode(",", $tmpTables);
        } else {
            $tables = $this->wrap($tables);
            return $this->addTableSchema($tables);
        }
    }

    protected function addTableSchema($table)
    {
        $schemaTable = $this->wrapper . $this->schema . $this->wrapper . ".";
        if (strpos($table, "SELECT")) {
            $schemaTable = $table;
        } else {
            if (strpos($table, "AS")) {
                $arrTable = explode(" ", $table);
                $schemaTable .= $arrTable[0] . " " . $arrTable[1] . " " . $arrTable[2];
            } else {
                $schemaTable .= $table;
            }
        }
        
        return $schemaTable;
    }

    protected function wrapArray(array $values)
    {
        $wrapResult = array();
        foreach ($values as $value) {
            $wrapResult[] = $this->wrap($value);
        }

        return $wrapResult;
    } // wrapArray()

    protected function wrap($value)
    {
        $wrapResult = "";
        if ($value instanceof Raw) {
            if ($value->getParameters()) {
                $this->parameterWriter->merge($value->getParameters());
            }

            $wrapResult = $value;
        } else {
            if (strpos($value, "AS")) { // for values with aliases
                $arrValue = explode(" AS ", $value);

                $wrapAlias = $this->wrapper . trim($arrValue[1]) . $this->wrapper;

                if (strpos($arrValue[0], ".")) { // for select columns with table aliases
                    $arrFirstValue = explode(".", $arrValue[0]);

                    $col = $this->wrapper . trim($arrFirstValue[1]) . $this->wrapper;
                    $wrapResult = $arrFirstValue[0] . "." . $col;
                } else { // for tables
                    $wrapResult = $this->wrapper . trim($arrValue[0]) . $this->wrapper;
                }
                $wrapResult .= " AS " . $wrapAlias;
            } else {
                if (strpos($value, ".")) { // for select columns with table aliases
                    $arrVal = explode(".", $value);

                    $col = ($arrVal[1] == "*") ? $arrVal[1] : $this->wrapper . $arrVal[1] . $this->wrapper;

                    $wrapResult = $arrVal[0] . "." . $col;
                } else { // for everything else
                    $wrapResult = $this->wrapper . $value . $this->wrapper;
                }
            }
        }
        return trim($wrapResult);
    } // wrap()

    protected function parseStatements()
    {
        $fullQuery = "";
        foreach ($this->statements as $statement) {
            if (!empty($statement)) {
                $fullQuery .= $statement . "\n";
            }
        }
        return $fullQuery;
    }
}
