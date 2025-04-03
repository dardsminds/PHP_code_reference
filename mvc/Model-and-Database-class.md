Here's a complete PHP Model class that utilizes a separate Database class for handling database connections and queries. This implementation follows best practices with prepared statements and provides common CRUD operations:

### 1. First, the Database Class (Database.php):

```php
<?php

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    
    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database}",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
```

### 2. The Base Model Class (Model.php):

```php
<?php

require_once 'Database.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    // Find single record by primary key
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    // Get all records
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    // Create new record
    public function create(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        
        return $this->db->lastInsertId();
    }
    
    // Update record
    public function update($id, array $data) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->query($sql, $data);
        return $stmt->rowCount();
    }
    
    // Delete record
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount();
    }
    
    // Find records with conditions
    public function where(array $conditions, $operator = 'AND') {
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereParts[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $whereClause = implode(" {$operator} ", $whereParts);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause}";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Custom query execution
    public function rawQuery($sql, $params = []) {
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
}
```

### 3. Example Usage (User Model):

```php
<?php

require_once 'Model.php';

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    // Custom method specific to users
    public function getActiveUsers() {
        return $this->where(['status' => 'active']);
    }
    
    public function getAdmins() {
        $sql = "SELECT * FROM {$this->table} WHERE role = ? AND status = ?";
        return $this->rawQuery($sql, ['admin', 'active']);
    }
}
```

### 4. Implementation Example:

```php
<?php

// Initialize database connection
$db = new Database('localhost', 'db_username', 'db_password', 'db_name');

// Create user model instance
$userModel = new User($db);

// Create a new user
$newUserId = $userModel->create([
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'password' => password_hash('secret123', PASSWORD_DEFAULT),
    'status' => 'active',
    'role' => 'user'
]);

// Get user by ID
$user = $userModel->find($newUserId);

// Update user
$userModel->update($newUserId, ['email' => 'new_email@example.com']);

// Get all active users
$activeUsers = $userModel->getActiveUsers();

// Get all admin users
$admins = $userModel->getAdmins();

// Delete user
$userModel->delete($newUserId);
```

### Key Features:

1. **Separation of Concerns**: Database handling is separated from model logic
2. **Security**: Uses prepared statements to prevent SQL injection
3. **Extensibility**: Easy to add custom methods for specific models
4. **CRUD Operations**: Provides all basic create, read, update, delete operations
5. **Flexibility**: Includes raw query method for complex queries
