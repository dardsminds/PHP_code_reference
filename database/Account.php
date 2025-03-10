<?php

class Account extends Database {
    // Constructor to initialize the database connection using the parent constructor
    public function __construct() {
        parent::__construct(); // Call the parent constructor to establish the database connection
    }

    // Method to create a new account
    public function createAccount($username, $password, $email) {
        $username = $this->escapeString($username);
        $password = $this->escapeString($password);
        $email = $this->escapeString($email);

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO accounts (username, password, email) VALUES ('$username', '$hashedPassword', '$email')";

        if ($this->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    // Method to authenticate a user
    public function authenticate($username, $password) {
        $username = $this->escapeString($username);
        $password = $this->escapeString($password);

        $sql = "SELECT * FROM accounts WHERE username = '$username'";
        $result = $this->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                return true;
            }
        }

        return false;
    }

    // Method to get account details
    public function getAccountDetails($username) {
        $username = $this->escapeString($username);

        $sql = "SELECT * FROM accounts WHERE username = '$username'";
        $result = $this->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
}
