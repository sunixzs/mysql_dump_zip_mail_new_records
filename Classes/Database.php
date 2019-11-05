<?php

if (!defined('MODE') || MODE != 'INDEX') {
    die('Direct access denied!');
}

class Database
{
    /**
     * @var object
     */
    protected $link = null;

    /**
     * @param string $dbHost
     * @param string $dbUsername
     * @param string $dbPassword
     * @param string $db
     * @throws Exception
     */
    public function __construct($dbHost, $dbUsername, $dbPassword, $db)
    {
        Logger::log('connect to database.');
        @$this->link = new mysqli($dbHost, $dbUsername, $dbPassword, $db);

        if ($this->link->connect_errno) {
            throw new Exception(printf("Could not connect to database: %s\n", $this->link->connect_error));
        }

        $this->link->query('SET NAMES `utf8` COLLATE `utf8_general_ci`'); // Unicode
    }

    public function close()
    {
        Logger::log('close database connection.');
        $this->link->close();
    }

    /**
     * return the database connection.
     *
     * @return object
     */
    public function get()
    {
        return $this->link;
    }
}
