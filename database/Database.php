<?php

class Database {
    private $host = 'localhost'; // Database host
    private $username = 'root'; // Database username
    private $password = ''; // Database password
    private $dbname = 'test_db'; // Database name
    protected $connection;

    // Constructor to initialize the database connection
    public function __construct() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        // Check connection
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    // Method to execute a query
    public function query($sql) {
        return $this->connection->query($sql);
    }

    // Method to escape special characters in a string for use in an SQL statement
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }

    // Destructor to close the database connection
    public function __destruct() {
        $this->connection->close();
    }
}
