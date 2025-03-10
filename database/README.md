## PHP Database Connection and Account Class

# Example Usage
```php
<?php

// Include the classes
require_once 'Database.php';
require_once 'Account.php';

// Create an instance of the Account class
$account = new Account();

// Create a new account
if ($account->createAccount('john_doe', 'password123', 'john@example.com')) {
    echo "Account created successfully!";
} else {
    echo "Failed to create account.";
}

// Authenticate a user
if ($account->authenticate('john_doe', 'password123')) {
    echo "Authentication successful!";
} else {
    echo "Authentication failed.";
}

// Get account details
$accountDetails = $account->getAccountDetails('john_doe');
if ($accountDetails) {
    echo "Account Details: " . print_r($accountDetails, true);
} else {
    echo "Account not found.";
}
```
