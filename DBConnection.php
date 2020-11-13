<?php
namespace wcx;

/**
 * Connecting to a database and querying
 * @author Dimitrios Kastrinakis
 *
 */
class DBConnection {

    private $dbh;

    public function __construct($host, $dbName, $dbUser, $dbPass) {
        $this->dbh = new \PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    }

    public function getConnection() {
        return $this->dbh;
    }

    public function __destruct() {
        $this->dbh = null;
    }
}
?>
