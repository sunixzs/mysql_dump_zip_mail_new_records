<?php

if (!defined('MODE') || MODE != 'INDEX') {
    die('Direct access denied!');
}

/**
 * Creates a special mysqldump from a table with a key 'uid'.
 * Expects $GLOBALS["DB"] with a mysqli connection.
 */
class Mysqldump
{
    /**
     * @var string
     */
    public $dateString = '';

    /**
     * @var string
     */
    public $dbHost = '';

    /**
     * @var string
     */
    public $db = '';

    /**
     * @var string
     */
    public $dbTable = '';

    /**
     * @var int
     */
    protected $startUid = 0;

    /**
     * @var int
     */
    protected $endUid = 0;

    /**
     * @param string $dateString
     * @param string $dbHost
     * @param string $db
     * @param string $dbTable
     */
    public function __construct($dateString, $dbHost, $db, $dbTable)
    {
        $this->dateString = $dateString;
        $this->dbHost = $dbHost;
        $this->db = $db;
        $this->dbTable = $dbTable;
    }

    /**
     * It is hard possible to determine if there is data in the dump.
     * So here is an easy property with the value of it.
     *
     * @var bool
     */
    private $thereIsADump = false;

    /**
     * The main method to build and get the dump.
     *
     * @param int $startUid
     *
     * @return string
     */
    public function getDumpData($startUid)
    {
        $dump = '';
        Logger::log('build mysqldump ...');
        // Dump-Header
        Logger::log('... header');
        $dump = "\n/*---------------------------------------------------------------";
        $dump .= "\n  SQL DB BACKUP " . $this->dateString;
        $dump .= "\n  HOST: {$this->dbHost}";
        $dump .= "\n  DATABASE: {$this->db}";
        $dump .= "\n  TABLES: {$this->dbTable}";
        $dump .= "\n  ---------------------------------------------------------------*/\n";

        // Drop Table
        Logger::log('... drop table command');
        $dump .= "\n/*---------------------------------------------------------------";
        $dump .= "\n  DROP TABLE AND STRUCTURE FOR TABLE: `{$this->dbTable}`";
        $dump .= "\n  !!! DO NOT USE THIS, IF TABLE EXIST AND THERE IS DATA IN TABLE YOU NEED !!!";
        $dump .= "\n  REMOVE ALL TO COMMENT BELOW.";
        $dump .= "\n  ---------------------------------------------------------------*/\n";
        $dump .= "DROP TABLE IF EXISTS `{$this->dbTable}`;\n";

        Logger::log('... table structure');

        $resultCreateTable = $GLOBALS['DB']->query("SHOW CREATE TABLE `{$this->dbTable}`");
        if (!$resultCreateTable) {
            $this->thereIsADump = false;
            Logger::log("STOP: Could not get the table data of `{$this->dbTable}`!");

            return '';
        }
        $row = $resultCreateTable->fetch_object();
        $dump .= $row->{'Create Table'} . ";\n";

        $dump .= "\n/*---------------------------------------------------------------";
        $dump .= "\n  HERE IS THE COMMENT NAMED ABOVE TO REMOVE";
        $dump .= "\n  ---------------------------------------------------------------*/\n";

        // find the data
        Logger::log('... find the data');

        $this->startUid = $startUid;
        ++$this->startUid;
        $this->endUid = $this->startUid;
        $result = $GLOBALS['DB']->query("SELECT * FROM `{$this->dbTable}` WHERE uid >= {$this->startUid} ORDER BY uid ASC LIMIT 0,1000");
        $num_rows = $result->num_rows;

        if ($num_rows > 0) {
            Logger::log('... write inserts');
            $vals = [];
            $z = 0;
            for ($i = 0; $i < $num_rows; ++$i) {
                $items = $result->fetch_row();
                $vals[$z] = '(';
                for ($j = 0; $j < count($items); ++$j) {
                    if (isset($items[$j])) {
                        $vals[$z] .= "'" . $GLOBALS['DB']->real_escape_string($items[$j]) . "'";
                    } else {
                        $vals[$z] .= 'NULL';
                    }
                    if ($j < (count($items) - 1)) {
                        $vals[$z] .= ',';
                    }
                }
                $vals[$z] .= ')';
                ++$z;
                $this->endUid = $items[0];
            }
            $dump .= "INSERT INTO `{$this->dbTable}` VALUES ";
            $dump .= '  ' . implode(";\nINSERT INTO `{$this->dbTable}` VALUES ", $vals) . ";\n";
            $this->thereIsADump = true;
        } else {
            Logger::log('... no data - nothing to do: END!');
            $this->thereIsADump = false;

            return $dump;
        }

        Logger::log('... mysqldump built.');

        return $dump;
    }

    /**
     * @return int
     */
    public function getStartUid()
    {
        return $this->startUid;
    }

    /**
     * @return int
     */
    public function getEndUid()
    {
        return $this->endUid;
    }

    /**
     * @return bool
     */
    public function isThereADump()
    {
        return $this->thereIsADump;
    }
}
