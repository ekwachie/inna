# Database Migrations

Migration classes live in this directory. The maintained guide is [docs/database/migrations.md](../docs/database/migrations.md).

The bundled schema files, in execution order, are:

1. m01012024_000_create_inna_database.php
2. m01012024_001_create_users_table.php
3. m01012024_002_create_otp_verifications_table.php

They target MySQL/MariaDB. Review database privileges and compatibility with your existing schema before applying them. The users migration does not drop an incompatible existing users table.

Run ./migrations update from the project root. Successful migrations are recorded individually; the runner stops and exits nonzero at the first failure. MySQL DDL may commit implicitly. Inspect partial schema changes before retrying and use a single deployment runner.

Generate a reviewed starter with ./migrations add projects name:string. The generated class is only a starting point: add IDs, indexes, foreign keys and resumable behavior deliberately. There is no automatic rollback/down CLI command.

