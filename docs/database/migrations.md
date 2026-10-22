# Migrations

Migrations are PHP classes in migration/. Use a database backup and a single migration runner for deployment. MySQL DDL may commit implicitly; a failed schema change is not automatically reversible.

## Generate

```bash
./migrations add projects name:string owner_id:int
```

The command generates a timestamped class with up() and down(). Supported CLI types are string, text, int, bigint, boolean and timestamp. Names must be safe identifiers. Review generated SQL before applying it; the basic generator does not add foreign keys, indexes or an automatic ID for you.

## Apply

```bash
./migrations update
```

The runner executes pending PHP files in filename order and records each successful migration immediately. It stops on the first error and exits nonzero. Fix the failed operation before rerunning.

For a custom migration:

```php
<?php
namespace app\migrations;

use app\Core\Application;

class m20261022000100_create_projects
{
    public function up()
    {
        Application::$app->db->pdo->exec(
            'CREATE TABLE projects (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                owner_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL
            )'
        );
    }
}
```

## Bundled schema

The supplied migrations create the inna database, users and OTP-verification tables. The database-creation migration requires suitable privileges; provision the database separately and review these migrations when adapting the starter.

The users migration does not replace an incompatible existing table. Write a data-preserving migration for your application's existing schema.

## Recovery

Migration failure may leave partial DDL. Inspect the actual schema before retrying, make migration steps resumable where possible, and take a backup before destructive operations. There is no down/rollback CLI command; down() is available for a migration workflow you implement deliberately. Do not run migrations automatically on every HTTP request.

