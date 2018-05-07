<?php
/**
 * DB - A simple database class
 *
 * Original author of this class: Vivek Wicky Aswal.
 * Original git link: https://github.com/wickyaswal/indieteq-php-my-sql-pdo-database-class
 *
 * Modified to fit the querybuilder project.
 */

namespace Core\Database;

use Core\Logger;
use Core\Config\Parser;

class DB
{
    /**
     * @var PDO The PDO object for the database driver
     */
    private $pdo;

    /**
     * @var string The raw query sent as parameter
     */
    private $rawQuery;

    /**
     * @var PDOStatement The PDO statement object for prepared statements
     */
    private $sQuery;

    /**
     * @var Parser The Parser object for database settings
     */
    private $config;

    /**
     * @var boolean Determines if there is a current open connection
     */
    private $con = false;

    /**
     * @var Logger The Logger object used for logging errors
     */
    private $logger;

    /**
     * @var array An array of parameters to be supplied for the PDO statement
     */
    private $parameters = array();

    /**
     * @var string The server the database will connect to
     */
    private $server;

    /**
     * @var array An array containing the connection settings
     */
    private $settings = array();

    /**
     * @var string The current selected database name
     */
    private $schema;

    public function __construct($dbServer = DB_DEFAULT)
    {
        $this->logger = new Logger(LOGPATH . 'sql');
        $this->server = $dbServer;
        $this->config = new Parser(CONFIGPATH, 'database.php');
        $this->connect();
    }

    private function connect()
    {
        try {
            //Data Source Name
            $this->settings = $this->config->get($this->server);
            $driver = $this->settings["driver"];
            $dbName = $this->settings["dbname"];
            $hostName = $this->settings["hostname"];
            $userName = $this->settings["username"];
            $password = $this->settings["password"];
            $options = $this->settings['options'];

            $this->schema = $dbName;

            $dsn = $driver . ':dbname=' . $dbName . ';host=' . $hostName . '';

            //PDO Instantiation
            $this->pdo = new \PDO($dsn, $userName, $password, $options);
            
            //If connection succeeds set boolean to true.
            if ($this->pdo) {
                $this->con = true;
            }
        } catch (\PDOException $e) {
            //Write into log and display Exception
            echo $this->log($e->getMessage());
            exit;
        }
    }

    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * OPTIONAL: Use this method to close the active connection.
     * Set PDO object to NULL to close the connection
     * See: http://www.php.net/manual/en/pdo.connections.php
     */
    public function close()
    {
        $this->con = false;
        $this->pdo = null;
    }

    public function getLastId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @void
     *
     * Add the parameter to the parameter array
     * @param string $para
     * @param string $value
     */
    public function bind($para, $value)
    {
        $this->parameters[sizeof($this->parameters)] = [":" . $para , $value];
    }
    /**
     * @void
     *
     * Add more parameters to the parameter array
     * @param array $parray
     */
    public function bindMore($parray)
    {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }

    /**
    * Every method which needs to execute a SQL query uses this method.
    *
    * 1. If not connected, connect to the database.
    * 2. Prepare Query.
    * 3. Parameterize Query.
    * 4. Execute Query.
    * 5. On exception : Write Exception into the log + SQL query.
    * 6. Reset the Parameters.
    * @param array $parameters
    */
    private function init($parameters = "")
    {
        if (!$this->con) {
            $this->connect();
        }

        try {
            //Prepare query
            $this->sQuery = $this->pdo->prepare($this->rawQuery);
            
            //Add parameters to the parameter array
            $this->bindMore($parameters);

            //Bind parameters
            if (!empty($this->parameters)) {
                foreach ($this->parameters as $param => $value) {
                    $type = \PDO::PARAM_STR;
                    switch ('$value[1]') {
                        case is_numeric($value[1]):
                            $type = \PDO::PARAM_INT;
                            break;
                        case is_bool($value[1]):
                            $type = \PDO::PARAM_BOOL;
                            break;
                        case is_null($value[1]):
                            $type = \PDO::PARAM_NULL;
                            break;
                    }
                    // Add type when binding the values to the column
                    $this->sQuery->bindValue($value[0], $value[1], $type);
                }
            }
            
            //Execute SQL Query
            $this->sQuery->execute();
        } catch (\PDOException $e) {
           //Write error into log
            echo $this->log($e->getMessage());
            exit;
        }

        //Reset parameters
        $this->parameters = array();
    }

    /**
     * If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
     * If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
     *
     * @param  string $query
     * @param  array  $params
     * @param  int    $fetchmode
     * @return mixed
     */
    public function query($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
    {
        $this->rawQuery = trim(str_replace("\r", " ", $query));
        
        $this->init($params);
        
        $explodedQuery = explode(" ", preg_replace("/\s+|\t+|\n+/", " ", $this->rawQuery));

        $this->rawQuery = null;
        
        //Which SQL statement is used
        $statement = strtolower($explodedQuery[0]);
        
        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete' || $statement === 'replace') {
            return $this->sQuery->rowCount();
        } else {
            return null;
        }
    }

    public static function raw($value, $parameters = array())
    {
        return new Raw($value, $parameters);
    }

    /**
     * Writes the log and returns the exception
     *
     * @param  string $message
     * @return string
     */
    private function log($message)
    {
        $displayMessage = "Error Connecting to Database.";

        if (!empty($this->rawQuery)) {
            # Add the Raw SQL to the Log
            $message .= "\r\nRaw SQL : " . $this->rawQuery;
        }
        # Write into log
        $this->logger->write($message);
        
        return $displayMessage;
    }
}
