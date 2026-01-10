# Database Migrations

Migrations allow you to version control your database schema and easily share it with your team.

## Creating Migrations

### Using the Migration Tool

Create a new migration:

```bash
./migrations add create_posts_table title:varchar(255) content:text user_id:int created_at:timestamp
```

This creates a migration file in the `migration/` directory.

### Migration File Structure

Generated migration files look like this:

```php
<?php

namespace app\migrations;

use app\core\Application;

class m01012024_001_create_posts_table
{
    public function up()
    {
        $db = Application::$app->db;
        $db->pdo->exec("CREATE TABLE IF NOT EXISTS posts (
    title varchar(255),
    content text,
    user_id int,
    created_at timestamp
) ENGINE=INNODB;");
    }
    
    public function down()
    {
        $db = Application::$app->db;
        $db->pdo->exec("DROP TABLE IF EXISTS posts;");
    }
}
```

## Running Migrations

### Apply All Pending Migrations

```bash
./migrations update
```

This will:
1. Check which migrations have been applied
2. Run any pending migrations in order
3. Record applied migrations in the `migrations` table

## Manual Migration Creation

You can also create migration files manually:

```php
<?php

namespace app\migrations;

use app\core\Application;

class m01012024_002_add_status_to_users
{
    public function up()
    {
        $db = Application::$app->db;
        $db->pdo->exec("
            ALTER TABLE users 
            ADD COLUMN status VARCHAR(50) DEFAULT 'active' 
            AFTER email
        ");
    }
    
    public function down()
    {
        $db = Application::$app->db;
        $db->pdo->exec("
            ALTER TABLE users 
            DROP COLUMN status
        ");
    }
}
```

## Migration Methods

### up()

The `up()` method defines what happens when the migration is applied. This is where you create tables, add columns, etc.

### down()

The `down()` method defines how to reverse the migration. This is useful for rollbacks.

## Common Migration Patterns

### Creating a Table

```php
public function up()
{
    $db = Application::$app->db;
    $db->pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            user_id INT NOT NULL,
            status ENUM('draft', 'published') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=INNODB;
    ");
}
```

### Adding Columns

```php
public function up()
{
    $db = Application::$app->db;
    $db->pdo->exec("
        ALTER TABLE users 
        ADD COLUMN phone VARCHAR(20) AFTER email,
        ADD COLUMN bio TEXT AFTER phone
    ");
}

public function down()
{
    $db = Application::$app->db;
    $db->pdo->exec("
        ALTER TABLE users 
        DROP COLUMN phone,
        DROP COLUMN bio
    ");
}
```

### Modifying Columns

```php
public function up()
{
    $db = Application::$app->db;
    $db->pdo->exec("
        ALTER TABLE users 
        MODIFY COLUMN email VARCHAR(255) NOT NULL UNIQUE
    ");
}
```

### Creating Indexes

```php
public function up()
{
    $db = Application::$app->db;
    $db->pdo->exec("
        ALTER TABLE posts 
        ADD INDEX idx_created_at (created_at),
        ADD INDEX idx_status (status)
    ");
}
```

### Adding Foreign Keys

```php
public function up()
{
    $db = Application::$app->db;
    $db->pdo->exec("
        ALTER TABLE posts 
        ADD CONSTRAINT fk_user 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE
    ");
}
```

## Migration Naming Convention

Migrations are named with the pattern:
```
m[DDMMYYYY]_[sequence]_[description].php
```

Example:
- `m01012024_000_create_inna_database.php`
- `m01012024_001_create_users_table.php`
- `m01012024_002_create_posts_table.php`

The sequence number ensures migrations run in the correct order.

## Migration Tracking

The framework automatically creates a `migrations` table to track which migrations have been applied:

```sql
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    migration VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;
```

## Best Practices

1. **Always provide down()**: Make migrations reversible
2. **Use transactions**: Wrap migrations in transactions when possible
3. **Test migrations**: Test both up() and down() methods
4. **Don't modify existing migrations**: Create new migrations for changes
5. **Use descriptive names**: Make migration purposes clear
6. **Order matters**: Ensure migrations run in logical order

## Example: Complete Migration

```php
<?php

namespace app\migrations;

use app\core\Application;

class m01012024_003_create_comments_table
{
    public function up()
    {
        $db = Application::$app->db;
        
        try {
            $db->beginTransaction();
            
            $db->pdo->exec("
                CREATE TABLE IF NOT EXISTS comments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    user_id INT NOT NULL,
                    content TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_post_id (post_id),
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (status),
                    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=INNODB;
            ");
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
    
    public function down()
    {
        $db = Application::$app->db;
        $db->pdo->exec("DROP TABLE IF EXISTS comments;");
    }
}
```

## Troubleshooting

### Migration Already Applied

If a migration fails partway through, you may need to manually remove it from the `migrations` table:

```sql
DELETE FROM migrations WHERE migration = 'm01012024_001_create_users_table.php';
```

### Migration Order Issues

Ensure migrations are named with sequential numbers to maintain order.

## Next Steps

- [Database Basics](basics.md) - Learn about database operations
- [Models](models.md) - Learn about using models

