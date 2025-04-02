# PHP Model Class Example

Here's a basic PHP Model class that you can use as a foundation for your application:

```php
<?php

/**
 * Base Model Class
 * Provides basic CRUD operations and database interaction
 */
class Model
{
    /**
     * Database connection
     * @var PDO
     */
    protected $db;

    /**
     * Table name
     * @var string
     */
    protected $table;

    /**
     * Primary key column
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Model constructor.
     * @param PDO $db PDO database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a record by primary key
     * @param int $id
     * @return array|false
     */
    public function find($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all records
     * @return array
     */
    public function all()
    {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new record
     * @param array $data
     * @return int|false The ID of the newly created record
     */
    public function create(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update a record
     * @param int $id
     * @param array $data
     * @return int Number of affected rows
     */
    public function update($id, array $data)
    {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);

        $query = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Delete a record
     * @param int $id
     * @return int Number of affected rows
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    /**
     * Find records by conditions
     * @param array $conditions
     * @return array
     */
    public function where(array $conditions)
    {
        $whereParts = [];
        foreach ($conditions as $key => $value) {
            $whereParts[] = "{$key} = :{$key}";
        }
        $whereClause = implode(' AND ', $whereParts);

        $query = "SELECT * FROM {$this->table} WHERE {$whereClause}";
        $stmt = $this->db->prepare($query);

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

## How to Use This Model

1. First, extend this base Model class for your specific entities:

```php
class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    // You can add custom methods specific to users
    public function getActiveUsers()
    {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

2. Then use it in your application:

```php
// Create a PDO connection
$db = new PDO('mysql:host=localhost;dbname=your_db', 'username', 'password');

// Instantiate your model
$userModel = new User($db);

// Create a new user
$newUserId = $userModel->create([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);

// Get a user by ID
$user = $userModel->find($newUserId);

// Update a user
$userModel->update($newUserId, ['email' => 'new_email@example.com']);

// Delete a user
$userModel->delete($newUserId);
```

This is a basic implementation that you can extend with more features like:
- Validation
- Relationships
- Soft deletes
- Timestamps
- Query scopes
- Pagination
- etc.
