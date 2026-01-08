<?php

namespace app\migrations;

use app\Core\Application;

/**
 * Migration to create users table with bio data, phone, email verification, and phone verification
 */
class m01012024_001_create_users_table
{
    public function up()
    {
        $db = Application::$app->db;
        
        // Check if table exists and has the correct structure
        $tableExists = false;
        try {
            $stmt = $db->pdo->query("SHOW TABLES LIKE 'users'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                // Verify the id column exists
                $stmt = $db->pdo->query("SHOW COLUMNS FROM users WHERE Field = 'id'");
                if ($stmt->rowCount() === 0) {
                    // Table exists but doesn't have id column, drop and recreate
                    echo "Users table exists but is missing 'id' column. Dropping and recreating...\n";
                    $db->pdo->exec("DROP TABLE IF EXISTS users");
                    $tableExists = false;
                }
            }
        } catch (\PDOException $e) {
            // If we can't check, proceed with creation
            echo "Note: Could not verify users table structure: " . $e->getMessage() . "\n";
        }
        
        if (!$tableExists) {
            $db->pdo->exec("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20) UNIQUE,
            password VARCHAR(255) NOT NULL,
            
            -- Bio data
            firstname VARCHAR(100),
            lastname VARCHAR(100),
            middlename VARCHAR(100),
            date_of_birth DATE,
            gender ENUM('male', 'female', 'other'),
            profile_picture VARCHAR(255),
            bio TEXT,
            address TEXT,
            city VARCHAR(100),
            state VARCHAR(100),
            country VARCHAR(100),
            postal_code VARCHAR(20),
            
            -- Verification status
            email_verified TINYINT(1) DEFAULT 0,
            email_verified_at TIMESTAMP NULL,
            phone_verified TINYINT(1) DEFAULT 0,
            phone_verified_at TIMESTAMP NULL,
            
            -- Account status
            is_active TINYINT(1) DEFAULT 1,
            is_suspended TINYINT(1) DEFAULT 0,
            role VARCHAR(50) DEFAULT 'user',
            
            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            
            -- Indexes
            INDEX idx_email (email),
            INDEX idx_phone (phone),
            INDEX idx_username (username),
            INDEX idx_email_verified (email_verified),
            INDEX idx_phone_verified (phone_verified),
            INDEX idx_is_active (is_active),
            INDEX idx_role (role)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            
            echo "Users table created successfully\n";
        } else {
            echo "Users table already exists with correct structure, skipping creation\n";
        }
    }
    
    public function down()
    {
        $db = Application::$app->db;
        $db->pdo->exec("DROP TABLE IF EXISTS users");
        echo "Users table dropped\n";
    }
}

