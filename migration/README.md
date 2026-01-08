# Inna Framework Migrations

This directory contains database migrations for the Inna framework.

## Included Migrations

### 1. `m01012024_create_inna_database.php`
Creates the `inna` database with UTF8MB4 character set.

**Note:** This migration may need to be run manually if your DSN already specifies a database. To create the database manually:
```sql
CREATE DATABASE IF NOT EXISTS inna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. `m01012024_create_users_table.php`
Creates the `users` table with:
- **Bio data**: firstname, lastname, middlename, date_of_birth, gender, profile_picture, bio, address, city, state, country, postal_code
- **Contact**: email, phone
- **Verification**: email_verified, email_verified_at, phone_verified, phone_verified_at
- **Account management**: is_active, is_suspended, role, last_login
- **Timestamps**: created_at, updated_at

### 3. `m01012024_create_otp_verifications_table.php`
Creates the `otp_verifications` table for OTP-based verification:
- Supports multiple verification types: email, phone, password_reset, two_factor
- Tracks OTP codes with expiration
- Limits attempts (default: 5)
- Records IP address and user agent
- Links to users table via foreign key

## Running Migrations

To apply all migrations:
```bash
./migrations update
```

To create a new migration:
```bash
./migrations add table_name column:type column:type ...
```

## Migration Order

Migrations are applied in alphabetical order. The naming convention `m01012024_*` ensures proper ordering:
1. Database creation (if needed)
2. Users table
3. OTP verifications table (depends on users table)

## Database Setup

Before running migrations, ensure your `.env` file has the correct database configuration:

```env
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=inna;charset=utf8mb4
DB_USER=root
DB_PASSWORD=your_password
```

If the database doesn't exist yet, you may need to:
1. Connect to MySQL without specifying a database
2. Run the database creation migration manually
3. Then run the table migrations

