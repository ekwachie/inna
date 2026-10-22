# Database

## Configure and connect

Set DB_DSN, DB_USER and DB_PASSWORD in .env. The bundled schema targets MySQL/MariaDB. PHP 8.4 creates the Database object lazily; the first query opens PDO and enables exception mode.

```php
use app\Core\Application;

$db = Application::$app->db;
$rows = $db->select(
    'SELECT id, username FROM users WHERE id = :id',
    ['id' => 1]
);
```

select returns all matching rows as associative arrays by default. An empty result is []. Use $rows[0] ?? null for one row.

## Write records

```php
$id = $db->insert('projects', ['name' => 'Example']);
$affected = $db->update(
    'projects',
    ['name' => 'Renamed'],
    'id = :project_id',
    ['project_id' => $id]
);
$deleted = $db->delete('projects', 'id = :project_id', ['project_id' => $id]);
```

Create the projects table first. Table names, column names and WHERE expressions are SQL identifiers/fragments, not bound values. Keep them static or choose them from an explicit application allowlist. Bind all user-supplied values, including values in WHERE clauses. Use distinct placeholder names for updated fields and conditions.

## Transactions

```php
$result = $db->transaction(function ($db) {
    $id = $db->insert('projects', ['name' => 'Atomic example']);
    return $id;
});
```

An exception rolls back an active transaction and is rethrown. Nested transaction() calls are rejected; there is no implicit savepoint support. Filesystem writes and HTTP requests are not rolled back by PDO. Keep network calls outside database locks and coordinate file cleanup explicitly.

## Reference

DbModel supplies select(query, params, fetchMode), insert(table, data), update(table, data, where, whereParams), delete(table, where, params), prepare(sql), complexQuery(sql, params), id(), beginTransaction(), commit(), rollback(), inTransaction() and transaction(callback).

complexQuery returns execution success, not fetched rows. prepare returns a PDOStatement. Dialect-specific legacy methods such as insertIgnore and insertUpdate are not a portable query builder. Prefer explicit prepared SQL for advanced queries. SQLite powers tests; bundled migrations are not automatically portable to SQLite or PostgreSQL.

Continue with [models](database/models.md) and [migrations](database/migrations.md).

