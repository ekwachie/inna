<?php

namespace app\migrations;

use app\Core\Application;

/**
 * Migration to create the inna database
 * Note: This migration should be run manually or with a connection that doesn't specify a database
 */
class m01012024_000_create_inna_database
{
    public function up()
    {
        $db = Application::$app->db;
        
        // Create database if it doesn't exist
        // Note: This requires connecting without specifying a database in DSN
        try {
            $db->pdo->exec("CREATE DATABASE IF NOT EXISTS inna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Database 'inna' created successfully (or already exists)\n";
        } catch (\PDOException $e) {
            // If database already exists or connection issue, log it
            echo "Note: Database creation handled at connection level. " . $e->getMessage() . "\n";
        }
    }
    
    public function down()
    {
        $db = Application::$app->db;
        try {
            $db->pdo->exec("DROP DATABASE IF EXISTS inna");
            echo "Database 'inna' dropped\n";
        } catch (\PDOException $e) {
            echo "Error dropping database: " . $e->getMessage() . "\n";
        }
    }
}

