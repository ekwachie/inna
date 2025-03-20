<?php

namespace app\migrations;

use app\core\Application;

class m2032025_Dogusers
{
    public function up()
    {
        $db = Application::$app->db;
        $db->pdo->exec("CREATE TABLE IF NOT EXISTS Dogusers (
    id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB;");
    }
    
    public function down()
    {
        $db = Application::$app->db;
        $db->pdo->exec("DROP TABLE IF EXISTS Dogusers;");
    }
}
