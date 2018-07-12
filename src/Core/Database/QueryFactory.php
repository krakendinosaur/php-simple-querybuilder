<?php
/**
 * A factory class for standard query syntax.
 *
 * PHP Version 5.6.35
 *
 * @author Prince Ryan Sy
 */

namespace Core\Database;

class QueryFactory
{
    /**
     * @var DB The database object
     */
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * Instantiates a new Select object.
     *
     * @param DB The database instance
     */
    public function select(DB $db = null)
    {
        $select = null;
        if ($db) {
            $select = new Select($db);
        } else {
            $select = new Select($this->db);
        }
        return $select;
    }

    /**
     * Instantiates a new Insert object.
     *
     * @param DB The database instance
     */
    public function insert(DB $db = null)
    {
        $insert = null;
        if ($db) {
            $insert = new Insert($db);
        } else {
            $insert = new Insert($this->db);
        }
        return $insert;
    }

    /**
     * Instantiates a new Update object.
     *
     * @param DB The database instance
     */
    public function update(DB $db = null)
    {
        $update = null;
        if ($db) {
            $update = new Update($db);
        } else {
            $update = new Update($this->db);
        }
        return $update;
    }

    /**
     * Instantiates a new Delete object.
     *
     * @param DB The database instance
     */
    public function delete(DB $db = null)
    {
        $delete = null;
        if ($db) {
            $delete = new Delete($db);
        } else {
            $delete = new Delete($this->db);
        }
        return $delete;
    }
}
