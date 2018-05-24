# Simple Query Builder
A simple stand-alone query builder. Made only to support MySQL for now and created with PHP Version 5.6.35. Automatically parameterizes values and sanitizes columns, tables, schema, etc.

## TO DO:
- Finish the readme
- Document Blocks

<a name = "table-of-contents"></a>

## Table of Contents

- [Features](#features)
- [Installation](#installation)
    - [Connection](#connection)
    - [Load DB and QueryFactory Classes](#load-db-and-queryfactory-classes)
    - [Multiple Connections](#multiple-connections)
- [Debugging and Execution](#debugging-and-execution)
- [Error Logging](#error-logging)
    - [SQL Logging](#sql-logging)
    - [Query Builder Logging](#query-builder-logging)
        - [Required Where Clause](#required-where-clause)
        - [Required Group By on Having](#required-group-by-on-having)
- [Insert Query](#insert-query)
    - [Insert Ignore](#insert-ignore)
    - [Replace Into](#replace-into)
- [Update Query](#update-query)
- [Delete Query](#delete-query)
- [Select Query](#select-query)
    - [Simple](#simple)
    - [With Specified Columns](#with-specified-columns)
    - [With Alias](#with-alias)
    - [Joins](#joins)
        - [Full Outer Join](#full-outer-join)
        - [Inner Join](#inner-join)
        - [Left Join](#left-join)
        - [Right Join](#right-join)
- [Where](#where)
    - [Simple Where](#simple-where)
    - [Where Between and Where Not Between](#where-between-and-where-not-between)
    - [Where In and Where Not In](#where-in-and-where-not-in)
    - [Where Null and Where Not Null](#where-null-and-where-not-null)
    - [Where Exists and Where Not Exists](#where-exists-and-where-not-exists)
    - [Nested Where](#nested-where)
- [Group By](#group-by)
- [Having](#having)
    - [Nested Having](#nested-having)
- [Order By](#order-by)
- [Limit](#limit)
- [Raw Expressions](#raw-expressions)
- [Raw Queries](#raw-queries)
- [The Get Query Method](#the-get-query-method)
- [Freestyle Coding](#freestyle-coding)

## Features[▲](#table-of-contents)

- Select
- Update
- Delete
- Insert
- Raw Expressions
- Nested Expressions/Queries
- Auto Parameterized Values
- Query Error logging
- Config file parser (php or json)

## Installation[▲](#table-of-contents)

Does not yet have a composer installation so you'll have to clone the project:

SSH:

    git clone git@github.com:princesy/simple-querybuilder.git

HTTPS:

    git clone https://github.com/princesy/simple-querybuilder.git

### Connection[▲](#table-of-contents)

Fixed location: application/config/database.php

First, create a folder named config inside the application folder. Then create your parsable php file named database.php.

Sample Code:

```PHP
return array
(
    'local' => array
    (
        'hostname'     => 'localhost',
        'dbname'       => 'testdb',
        'username'     => 'root',
        'password'     => '',
        'options'      => array
        (
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            //Charset for data rendering
            \PDO::ATTR_PERSISTENT => false,
            //Checks an established connection to prevent multiple connections
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            //PDO error handling, throws an error and allows you to handle it.
            \PDO::ATTR_EMULATE_PREPARES => false
            //Enables or disables emulation of prepared statements
        )
    ), // local connection
);
```

### Load DB and QueryFactory Classes[▲](#table-of-contents)

Example:

```PHP
require_once('common/init.php');

use Core\Database\DB;
use Core\Database\QueryFactory;

$db = new DB('local');
$qb = new QueryFactory($db);
```

### Multiple Connections[▲](#table-of-contents)

You can have multiple connection instance. As long as it exists in the database.php file in the config folder.

```PHP
$db = new DB('local');
$otherDb = new DB('otherDb');
$qb = new QueryFactory($db);
```

You can also pass the db instance to the query factory without the need to re-instantiate it.

```PHP
$qb->select($db);
$qb->insert($otherDb);
```

## Debugging and Execution[▲](#table-of-contents)

Call the debug() method to print parameters (if any) and the full query.

Code:

```PHP
$qb
->select()
->table('persons')
->debug();
```

Output:

```SQL
SELECT *
FROM
`testdb`.`persons`
```

Call the exec() method to execute the query and return the rowcount or resultset.

Code:

```PHP
$qb
->select()
->table('persons')
->exec()
```

## Error Logging[▲](#table-of-contents)

- The logger creates a new file daily in the format Y-m-d (e.g. 2018-05-21.log).
- Every log will always be headed by a timestamp and will be followed below by the full exception message.
- The default logging path is located in application/logs and will automatically create the necessary folders if it doesn't exists.
Note: You can modify this by going to common/default.php file and edit the LOGPATH constant.

### SQL Logging[▲](#table-of-contents)

- The logs are located in application/logs/sql.
- Every time a connection error is encountered a message "Error Connecting to Database" will be displayed and the full exception message can be found in the log file.
- Every time a query error is encountered a message "A query error was encountered" will be displayed and the full exception message can be found in the log file along with the raw Query ran.
<br/>
Sample log entry:

```
Time : 13:58:43
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'testdb.person' doesn't exist
Raw SQL : SELECT a.`fname`,a.`lname`,a.`gender` FROM `testdb`.`person` AS `a` WHERE `id` = :v1
```

### Query Builder Logging[▲](#table-of-contents)

The logs are located in application/logs/querybuilder.

#### Required Where Clause[▲](#table-of-contents)

WHERE Clause is always required in update or delete queries or else it will return an exception.<br/>

Sample log entry:

```
Time : 11:33:41
Error: Where clause is required in DELETE syntax.
```

#### Required Group By on Having[▲](#table-of-contents)

GROUP BY Clause is always required when your Select query includes a having clause or else it will return an exception.<br/>

Sample log entry:

```
Time : 11:55:53
Error: GROUP BY is required for HAVING clause
```

## Insert Query[▲](#table-of-contents)

Code:

```PHP
$result = $qb
->insert()
->table('persons')
->values([
    'fname' => 'Prince Ryan',
    'lname' => 'Sy',
    'gender' => 'M',
    'age' => 26
]);

$result->debug();
```

Output:

```SQL
Array
(
    [v1] => Prince Ryan
    [v2] => Sy
    [v3] => M
    [v4] => 26
)

INSERT INTO
`testdb`.`persons`
SET
`fname` = :v1,
`lname` = :v2,
`gender` = :v3,
`age` = :v4
```

### Insert Ignore[▲](#table-of-contents)

Just add the ignore() method to the insert instance.

Code:

```PHP
$result = $qb
->insert()
->ignore()
->table('persons')
->values([
    'fname' => 'Prince Ryan',
    'lname' => 'Sy',
    'gender' => 'M',
    'age' => 26
]);

$result->debug();
```

Output:

```SQL
Array
(
    [v1] => Prince Ryan
    [v2] => Sy
    [v3] => M
    [v4] => 26
)

INSERT IGNORE INTO
`testdb`.`persons`
SET
`fname` = :v1,
`lname` = :v2,
`gender` = :v3,
`age` = :v4
```

### Replace Into[▲](#table-of-contents)

Code:

```PHP
$result = $qb
->insert()
->replace()
->table('persons')
->values([
    'fname' => 'Prince Ryan',
    'lname' => 'Sy',
    'gender' => 'M',
    'age' => 26
]);

$result->debug();
```

Output:

```SQL
Array
(
    [v1] => Prince Ryan
    [v2] => Sy
    [v3] => M
    [v4] => 26
)

REPLACE INTO
`testdb`.`persons`
SET
`fname` = :v1,
`lname` = :v2,
`gender` = :v3,
`age` = :v4
```

## Update Query[▲](#table-of-contents)

Note: WHERE is strictly required in update query or else it will throw an exception. See [Required Where Clause](#required-where-clause)

Code:

```PHP
$result = $qb
->update()
->table('persons')
->values([
    'fname' => 'Prince Ryan',
    'lname' => 'Sy',
    'gender' => 'M',
    'age' => 26
])
->where("id", 1);

$result->debug();
```

Output:

```SQL
Array
(
    [v1] => 1
    [v2] => Prince Ryan
    [v3] => Sy
    [v4] => M
    [v5] => 26
)

UPDATE
`testdb`.`persons`
SET
`fname` = :v2,
`lname` = :v3,
`gender` = :v4,
`age` = :v5
WHERE `id` = :v1
```

## Delete Query[▲](#table-of-contents)

Note: WHERE is strictly required in delete query or else it will throw an exception. See [Required Where Clause](#required-where-clause)

Code:

```PHP
$delete = $qb->delete();
$result = $delete
->table('persons')
->where('id', 1);

$result->debug();
```

Output:

```SQL
Array
(
    [v1] => 27
)

DELETE FROM
`testdb`.`persons`
WHERE `id` = :v1
```

## Select Query[▲](#table-of-contents)

### Simple[▲](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->table('persons');

$result->debug();
```

Output:

```SQL
SELECT *
FROM
`testdb`.`persons`
```

### With Specified Columns[▲](#table-of-contents)

Accepts array:

Code:

```PHP
$result = $qb
->select()
->columns("fname", "lname", "gender")
->table('persons');

$result->debug();
```

or simple parameters:

Code:

```PHP
$result = $qb
->select()
->columns(["fname", "lname", "gender"])
->table('persons');

$result->debug();
```

Output:

```SQL
SELECT `fname`,`lname`,`gender`
FROM
`testdb`.`persons`
```

### With Alias[▲](#table-of-contents)

Note: AS keyword is required in table alias.

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons AS a');

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons` AS `a`
```

### Joins[▲](#table-of-contents)

#### Full Outer Join[▲](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons AS a')
->fullJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output

```Output
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons` AS `a`
FULL OUTER JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

#### Inner Join[▲](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons AS a')
->innerJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons` AS `a`
INNER JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

#### Left Join[▲](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons AS a')
->leftJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons` AS `a`
LEFT JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

You can also turn it into an outer join by setting the fifth parameter to true (default: false). The same goes for right join.

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons AS a')
->leftJoin('other_table b', 'a.id', '=', DB::raw('b.id'), true);

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons` AS `a`
LEFT OUTER JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

#### Right Join[▲](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons AS a')
->rightJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons` AS `a`
RIGHT JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

## Where[▲](#table-of-contents)

### Simple Where[▲](#table-of-contents)

Accepts 2 parameters (columnName, value) and operator is automatically "=":

Code:

```PHP
->where('name', 'Prince')
->orWhere('lname', 'Sy');
```

or 3 parameters (columnName, operator, value):

Code:

```PHP
->where('name', 'LIKE', '%search%')
->orWhere('age', '>', 20);
```

### Where Between and Where Not Between

Accepts strictly 3 parameters (columnName, from, to):

Code:

```PHP
->whereBetween('age', 20, 30)
->orWhereBetween('age', 30, 40)
->whereNotBetween('age', 20, 30)
->orWhereNotBetween('age', 30, 40);
```

### Where In and Where Not In

Accepts 2 parameters (columnName, arrayValues):

Code:

```PHP
->whereIn('age', [20, 23, 25])
->orWhereIn('age', [26, 27, 28])
->whereNotIn('age', [20, 23, 25])
->orWhereNotIn('age', [26, 27, 28]);
```

### Where Null and Where Not Null

Accepts 1 parameter (columnName):

Code:

```PHP
->whereNull('gender')
->orWhereNull('gender')
->whereNotNull('gender')
->orWhereNotNull('gender')
```

### Where Exists and Where Not Exists

Accepts 3 parameters (table, columnName, Closure):

Code:

```PHP
->whereExists('other_table', 'field', function ($q) {
    $q->where('id', DB::raw('mytable.id'));
})
->orWhereExists('other_table', 'field', function ($q) {
    $q->where('id', DB::raw('mytable.id'));
})
->whereNotExists('other_table', 'field', function ($q) {
    $q->where('id', DB::raw('mytable.id'));
})
->orWhereNotExists('other_table', 'field', function ($q) {
    $q->where('id', DB::raw('mytable.id'));
});
```

### Nested Where

Note: You can have an infinite depth of nested expressions.

Code:

```PHP
->where(function ($q) {
    $q->where('fname', '=', 'Prince')
    ->orWhere('lname', '=', 'Sy');
})
->orWhere(function ($q) {
    $q->where('fname', '=', 'Prince')
    ->orWhere('lname', '=', 'Sy');
});
```

## Group By[▲](#table-of-contents)

Accepts array:

Code:

```PHP
->groupBy(['age', 'lname']);
```

or simple parameters:

Code:

```PHP
->groupBy('age', 'lname');
```

## Having[▲](#table-of-contents)

Code:

```PHP
->having('age', '>=', 20)
->orHaving('age', '<=', 30);
```

### Nested Having[▲](#table-of-contents)

Note: You can have an infinite depth of nested expressions.

Code:

```PHP
->having(function ($q) {
    $q->having('age', '>=', 20)
    ->orHaving('age', '<=', 30);
})
->orHaving(function ($q) {
    $q->having('age', '>=', 20)
    ->orHaving('age', '<=', 30);
});
```

## Order By[▲](#table-of-contents)

Accepts array:

```PHP
->orderBy([]
    'age' => 'ASC',
    'lname' => 'DESC'
]);
```

or 2 parameters:

```PHP
->orderBy('age', 'ASC')
->orderBy('lname', 'DESC');
```

## Limit[▲](#table-of-contents)

Accepts 1 parameter (limit)

```PHP
->limit(10);
```

or 2 parameters (offset, limit)

```PHP
->limit(5, 10);
```

## Raw Expressions[▲](#table-of-contents)

Accepts 1 parameter (expression):

Code:

```PHP
$qb
->select()
->columns('a.field1', 'a.field2', DB::raw('AVG(b.field3)'))
->table('mytable AS a', DB::raw('(SELECT * FROM other_table) AS b'))
->debug();
```

Output:

```SQL
SELECT a.`field1`,a.`field2`,AVG(b.field3)
FROM
`testdb`.`mytable` AS `a`,(SELECT * FROM other_table) AS b
```

or 2 parameters (expression, arrayBindings):

Code:

```PHP
$qb
->select()
->columns('a.field1', 'a.field2', DB::raw('AVG(b.field3)'))
->table('mytable AS a', DB::raw('(SELECT * FROM other_table) AS b'))
->where(DB::raw('date(date_field) >= :dateField', ['dateField' => '2018-05-20']))
->debug();
```

Output:

```SQL
Array
(
    [dateField] => 2018-05-20
)

SELECT a.`field1`,a.`field2`,AVG(b.field3)
FROM
`testdb`.`mytable` AS `a`,(SELECT * FROM other_table) AS b
WHERE date(date_field) >= :dateField
```


## Raw Queries[▲](#table-of-contents)

Accepts 1 parameter (query):

Code:

```PHP
$db->query("SELECT * FROM persons WHERE name = 'Prince'");
//returns the resultset.
```

Accepts 2 parameters (query, bindings):

```PHP
$db->query("SELECT * FROM persons WHERE name = :name", ['name' => 'Prince']);
//returns the resultset.
```

## The Get Query Method[▲](#table-of-contents)

This method returns the full built query as raw output.

Code:

```PHP
echo $qb
->select()
->columns('a.field1', 'a.field2', 'b.field3')
->table('table1 AS a', 'table2 AS b')
->where('a.field1', '=', DB::raw('b.field1'))
->whereBetween('a.fieldBetween', 20, 40)
->whereIn('a.fieldIn', ['10', '20', '30'])
->getQuery();
```

Output:

```SQL
SELECT a.`field1`,a.`field2`,b.`field3` FROM `testdb`.`table1` AS `a`,`testdb`.`table2` AS `b` WHERE a.`field1` = b.field1 AND a.`fieldBetween` BETWEEN 20 AND 40 AND a.`fieldIn` IN ('10','20','30')
```

## Freestyle Coding

You can repeat methods as you see fit. You can also wrap them through conditions, loops, etc.<br/>
Note: Also applicable in all query types. (SELECT, INSERT, UPDATE, DELETE)

Code:

```PHP
$select = $qb->select();

$select
->columns('a.field1', 'a.field2')
->table('table1 AS a')
->where('a.field1', 'value');

if ($condition === true) {
    $select
    ->columns('b.field1', 'b.field2')
    ->table('table2 AS b')
    ->where('a.field1', DB::raw('b.field1'));
}

$select->debug();
```


if condition is true:

Output:

```SQL
Array
(
    [v1] => value
)

SELECT a.`field1`,a.`field2`,b.`field1`,b.`field2`
FROM
`testdb`.`table1` AS `a`,`testdb`.`table2` AS `b`
WHERE a.`field1` = :v1
AND a.`field1` = b.field1
```

if condition is false:

Output:

```SQL
Array
(
    [v1] => value
)

SELECT a.`field1`,a.`field2`
FROM
`testdb`.`table1` AS `a`
WHERE a.`field1` = :v1
```
