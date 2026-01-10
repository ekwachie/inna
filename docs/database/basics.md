# Database Basics

Inna provides a powerful database abstraction layer built on PDO, making database operations simple and secure.

## Database Connection

The database connection is automatically established when the Application is initialized. Access it via:

```php
use app\Core\Application;

$db = Application::$app->db;
```

## Configuration

Configure your database in `.env`:

```env
DB_DSN=mysql:host=localhost;dbname=my_database
DB_USER=root
DB_PASSWORD=your_password
```

## Basic Queries

### SELECT Queries

```php
// Simple select
$users = $db->select("SELECT * FROM users");

// With parameters
$user = $db->select(
    "SELECT * FROM users WHERE id = :id",
    ['id' => 1]
);

// Multiple conditions
$users = $db->select(
    "SELECT * FROM users WHERE email = :email AND status = :status",
    [
        'email' => 'john@example.com',
        'status' => 'active'
    ]
);
```

### INSERT Queries

```php
// Insert single record
$id = $db->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);

// Returns the last insert ID
echo "New user ID: $id";
```

### UPDATE Queries

```php
// Update with WHERE clause
$affected = $db->update(
    'users',
    [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com'
    ],
    'id = :id',
    ['id' => 1]
);

// Returns number of affected rows
echo "Updated $affected rows";
```

### DELETE Queries

```php
// Delete records
$deleted = $db->delete(
    'users',
    'id = :id',
    ['id' => 1]
);

// Returns number of deleted rows
echo "Deleted $deleted rows";
```

## Advanced Operations

### INSERT IGNORE

Insert or skip if duplicate:

```php
$id = $db->insertIgnore('users', [
    'email' => 'john@example.com',
    'name' => 'John'
]);
```

### INSERT ON DUPLICATE KEY UPDATE

Insert or update if key exists:

```php
$id = $db->insertUpdate('users', [
    'email' => 'john@example.com',
    'name' => 'John Doe',
    'updated_at' => date('Y-m-d H:i:s')
]);
```

### REPLACE

Replace existing record:

```php
$db->replace('users', [
    'id' => 1,
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

## Complex Queries

### JOIN Queries

```php
$results = $db->select("
    SELECT u.*, p.title 
    FROM users u 
    LEFT JOIN posts p ON u.id = p.user_id 
    WHERE u.id = :id
", ['id' => 1]);
```

### Aggregate Functions

```php
$count = $db->select(
    "SELECT COUNT(*) as total FROM users WHERE status = :status",
    ['status' => 'active']
);

$total = $count[0]['total'];
```

### Subqueries

```php
$users = $db->select("
    SELECT * FROM users 
    WHERE id IN (
        SELECT user_id FROM posts WHERE published = 1
    )
");
```

## Transactions

### Basic Transaction

```php
try {
    $db->beginTransaction();
    
    $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
    $userId = $db->id();
    
    $db->insert('profiles', ['user_id' => $userId, 'bio' => 'Developer']);
    
    $db->commit();
    echo "Transaction successful";
} catch (Exception $e) {
    $db->rollback();
    echo "Transaction failed: " . $e->getMessage();
}
```

### Transaction Methods

```php
$db->beginTransaction();  // Start transaction
$db->commit();            // Commit changes
$db->rollback();          // Rollback changes
```

## Fetch Modes

### Default (Associative Array)

```php
$users = $db->select("SELECT * FROM users");
// Returns: [['id' => 1, 'name' => 'John'], ...]
```

### Change Fetch Mode

```php
use PDO;

// Fetch as objects
$db->setFetchMode(PDO::FETCH_OBJ);
$users = $db->select("SELECT * FROM users");
// Returns: [(object)['id' => 1, 'name' => 'John'], ...]

// Fetch as class instances
$db->setFetchMode(PDO::FETCH_CLASS, 'User');
$users = $db->select("SELECT * FROM users");

// Override for single query
$users = $db->select("SELECT * FROM users", [], PDO::FETCH_OBJ);
```

## Utility Methods

### Get Last Insert ID

```php
$id = $db->id();
// Or
$id = Application::$app->db->pdo->lastInsertId();
```

### Show Query

For debugging, see the last executed query:

```php
$db->select("SELECT * FROM users WHERE id = :id", ['id' => 1]);
echo $db->showQuery();
// Output: SELECT * FROM users WHERE id = :id
```

### Show Columns

Get table structure:

```php
$columns = $db->showColumns('users');
// Returns: ['primary' => 'id', 'column' => ['id' => 'int', 'name' => 'varchar', ...]]
```

## Best Practices

1. **Always use parameters**: Never concatenate user input into queries
2. **Use transactions**: For multiple related operations
3. **Handle errors**: Wrap database operations in try-catch blocks
4. **Validate input**: Validate data before inserting/updating
5. **Use prepared statements**: The framework uses PDO prepared statements automatically

## Example: Complete CRUD Operation

```php
use app\Core\Application;

class UserController extends Controller
{
    public function create(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $body = $request->getBody();
            $db = Application::$app->db;
            
            try {
                $id = $db->insert('users', [
                    'name' => $body['name'],
                    'email' => $body['email'],
                    'password' => password_hash($body['password'], PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                echo $this->setFlash('success', 'User created!');
                return $response->redirect('/user/' . $id);
            } catch (Exception $e) {
                echo $this->setFlash('error', 'Error: ' . $e->getMessage());
            }
        }
        
        return $this->render('users/create');
    }
    
    public function update(Request $request, Response $response)
    {
        $id = $request->getRouteParam('id');
        $db = Application::$app->db;
        
        if ($request->isPost()) {
            $body = $request->getBody();
            
            $db->update(
                'users',
                [
                    'name' => $body['name'],
                    'email' => $body['email']
                ],
                'id = :id',
                ['id' => $id]
            );
            
            echo $this->setFlash('success', 'User updated!');
            return $response->redirect('/user/' . $id);
        }
        
        $user = $db->select(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $id]
        );
        
        return $this->render('users/edit', ['user' => $user[0]]);
    }
}
```

## Next Steps

- [Query Builder](query-builder.md) - Learn about the query builder
- [Migrations](migrations.md) - Learn about database migrations
- [Models](models.md) - Learn about using models

