# Simple Query Builder
A simple stand-alone query builder. Made only to support MySQL for now and created with PHP Version 5.6.35. Automatically parameterizes values and sanitizes columns, tables, schema, etc.

## TO DO:
- subwhere
- code super optimization

<a name = "table-of-contents"></a>

## Table of Contents

- [Features](#features)
- [Installation](#installation)
    - [Connection](#connection)
    - [Load DB and QueryFactory Classes](#load-db-and-queryfactory-classes)
    - [Multiple Connections](#multiple-connections)
- [Debugging and Execution](#debugging-and-execution)
- [Select Query](#select-query)
    - [Simple](#simple)
    - [With Specified Columns](#with-specified-columns)
    - [With Alias](#with-alias)
    - [Joins](#joins)
        - [Full Outer Join](#full-outer-join)
        - [Inner Join](#inner-join)
        - [Left Join](#left-join)
        - [Right Join](#right-join)
- [Insert Query](#insert-query)
    - [Insert Ignore](#insert-ignore)
    - [Replace Into](#replace-into)
- [Update Query](#update-query)

## Features[↑](#table-of-contents)

- Select
- Update
- Delete
- Insert
- Raw Expressions
- Nested Expressions/Queries
- Auto Parameterized Values
- Query Error logging
- Config file parser (php or json)

## Installation[↑](#table-of-contents)

Does not yet have a composer installation so you'll have to clone the project:

SSH:

    git clone git@github.com:princesy/simple-querybuilder.git

HTTPS:

    git clone https://github.com/princesy/simple-querybuilder.git

### Connection[↑](#table-of-contents)

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

### Load DB and QueryFactory Classes[↑](#table-of-contents)

Example:

```PHP
require_once('common/init.php');

use Core\Database\DB;
use Core\Database\QueryFactory;

$db = new DB('local');
$qb = new QueryFactory($db);
```

### Multiple Connections[↑](#table-of-contents)

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

## Debugging and Execution[↑](#table-of-contents)

Call the debug() method to print parameters (if any) and the full query.
___
Call exec() to execute the query and return the rowcount or resultset.

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

Code:

```PHP
$qb
->select()
->table('persons')
->exec()
```

## Select Query[↑](#table-of-contents)

### Simple[↑](#table-of-contents)

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

### With Specified Columns[↑](#table-of-contents)

There are two ways you can declare columns. As simple parameters or an array.

Code:

As Parameters:
```PHP
$result = $qb
->select()
->columns("fname", "lname", "gender")
->table('persons');

$result->debug();
```

OR

As Array:
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

### With Alias[↑](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons a');

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons a`
```

### Joins[↑](#table-of-contents)

#### Full Outer Join[↑](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons a')
->fullJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output

```Output
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons a`
FULL OUTER JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

#### Inner Join[↑](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons a')
->innerJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons a`
INNER JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

#### Left Join[↑](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons a')
->leftJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons a`
LEFT JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

You can also turn it into an outer join by setting the fifth parameter to true (default: false). The same goes for right join.

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons a')
->leftJoin('other_table b', 'a.id', '=', DB::raw('b.id'), true);

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons a`
LEFT OUTER JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

#### Right Join[↑](#table-of-contents)

Code:

```PHP
$result = $qb
->select()
->columns("a.fname", "a.lname", "a.gender")
->table('persons a')
->rightJoin('other_table b', 'a.id', '=', DB::raw('b.id'));

$result->debug();
```

Output:

```SQL
SELECT a.`fname`,a.`lname`,a.`gender`
FROM
`testdb`.`persons a`
RIGHT JOIN `testdb`.`other_table b`
ON a.`id` = b.id
```

## Insert Query[↑](#table-of-contents)

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

### Insert Ignore[↑](#table-of-contents)

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

### Replace Into[↑](#table-of-contents)

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

## Update Query[↑](#table-of-contents)

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

Capabilities:

Select:

- raw expressions
- 'AS' keyword required in all types of aliases
- columns - accepts array or arguments/parameters
- tables - accepts array or arguments/parameters
- fullJoin - strictly 4 arguments/parameters
- innerJoin - strictly 4 arguments/parameters
- leftJoin - strictly 4 or 5 arguements/parameters
- rightJoin - strictly 4 or 5 arguements/parameters
- standard where - strictly 2 or 3 arguments/parameters
- orwhere - strictly 2 or 3 arguments/parameters
- wherebetween - strictly 3 arguments/parameters
- orwherebetween - strictly 3 arguments/parameters
- wherein - strictly 2 arguments/parameters
- orwherein - strictly 2 arguments/parameters
- wherenull - strictly 1 argument/parameter
- whereisnotnull - strictly 1 argument/parameter
- groupby - accepts array or arguments/parameters
- having - strictly 2 or 3 arguments/parameters
- orhaving - strictly 2 or 3 arguments/parameters
- nested expressions - extends all where methods
- orderby - accepts array or strictly 2 arguments/parameters
- limit - strictly 1 or 2 arguments/parameters

Insert:

- raw expressions
- tables - accepts array or arguments/parameters
- values - accepts only array format

Update:

- raw expressions
- tables - accepts array or arguments/parameters
- values - accepts only array format
- WHERE clause is strictly required
- can access all wheres in select

Delete:

- raw expressions
- tables - accepts array or arguments/parameters
- WHERE clause is strictly required
- can access all wheres in select