# Models

Extend DbModel for application persistence. This is a thin PDO helper, not an Active Record implementation.

## Define a model

```php
<?php
namespace app\Models;

use app\Core\DbModel;

final class Project extends DbModel
{
    public function ownedBy(int $userId): array
    {
        return $this->select(
            'SELECT id, name FROM projects WHERE owner_id = :owner',
            ['owner' => $userId]
        );
    }

    public function createProject(int $userId, string $name): string
    {
        return $this->insert('projects', [
            'owner_id' => $userId,
            'name' => $name,
        ]);
    }
}
```

Create the table with id, owner_id and name before using this model. Call it from an authorized controller or application service.

## Validation and authorization

DbModel extends Model, so validation methods are available, but insertion does not automatically validate data. The caller must validate types and enforce ownership.

There are no built-in find(), findAll(), save() or relationships. The bundled User model defines its own createUser(), authenticate(), is_exist() and findOne(); its names are not framework-wide database APIs.

## Result handling

Keep database rows separate from API representations. Explicitly choose fields that may leave the server. Password hashes, encryption keys and private storage paths must not be returned.

See [database](database/basics.md) for binding and transaction behavior.

