<?php

namespace app\migrations;

use app\Core\Application;

/**
 * Migration to create OTP verifications table
 */
class m01012024_002_create_otp_verifications_table
{
    public function up()
    {
        $db = Application::$app->db;
        
        // Check if users table exists first
        $stmt = $db->pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            throw new \Exception("Users table must be created before OTP verifications table. Please run users migration first.");
        }
        
        // Verify users table has id column
        $stmt = $db->pdo->query("SHOW COLUMNS FROM users LIKE 'id'");
        if ($stmt->rowCount() === 0) {
            throw new \Exception("Users table exists but does not have an 'id' column. Please check the users table migration.");
        }
        
        // Create table without foreign key first
        $db->pdo->exec("CREATE TABLE IF NOT EXISTS otp_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            verification_type ENUM('email', 'phone', 'password_reset', 'two_factor') NOT NULL,
            identifier VARCHAR(255) NOT NULL COMMENT 'Email or phone number',
            otp_code VARCHAR(10) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            verified TINYINT(1) DEFAULT 0,
            verified_at TIMESTAMP NULL,
            attempts INT DEFAULT 0,
            max_attempts INT DEFAULT 5,
            ip_address VARCHAR(45),
            user_agent TEXT,
            
            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            -- Indexes
            INDEX idx_user_id (user_id),
            INDEX idx_identifier (identifier),
            INDEX idx_verification_type (verification_type),
            INDEX idx_otp_code (otp_code),
            INDEX idx_expires_at (expires_at),
            INDEX idx_verified (verified),
            INDEX idx_created_at (created_at)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Check if foreign key already exists
        $fkExists = false;
        try {
            $stmt = $db->pdo->query("SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'otp_verifications' 
                AND CONSTRAINT_NAME = 'fk_otp_user_id'");
            $fkExists = $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            // If we can't check, try to add it anyway
        }
        
        // Add foreign key constraint separately if it doesn't exist
        if (!$fkExists) {
            try {
                $db->pdo->exec("ALTER TABLE otp_verifications 
                    ADD CONSTRAINT fk_otp_user_id 
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
                echo "Foreign key constraint added successfully\n";
            } catch (\PDOException $e) {
                // Check if it's a duplicate constraint error
                if (strpos($e->getMessage(), 'Duplicate foreign key') !== false || 
                    strpos($e->getMessage(), 'already exists') !== false ||
                    strpos($e->getMessage(), 'Duplicate key name') !== false) {
                    echo "Foreign key constraint already exists, skipping...\n";
                } else {
                    // Re-throw if it's a different error
                    throw $e;
                }
            }
        } else {
            echo "Foreign key constraint already exists, skipping...\n";
        }
        
        echo "OTP verifications table created successfully\n";
    }
    
    public function down()
    {
        $db = Application::$app->db;
        $db->pdo->exec("DROP TABLE IF EXISTS otp_verifications");
        echo "OTP verifications table dropped\n";
    }
}

