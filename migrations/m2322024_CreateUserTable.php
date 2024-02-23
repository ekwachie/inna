<?php
/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Migrations
 *
 *
 */

use app\core\Application;

class m2322024_CreateUserTable
{
    public function up()
    {
        $db = Application::$app->db;
        // Query to create and alter goes here
        $SQL = "CREATE TABLE IF NOT EXISTS `users` (
                        `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
                        `fname` varchar(20) NOT NULL,
                        `lname` varchar(20) NOT NULL,
                        `email` varchar(60) NOT NULL,
                        `username` varchar(20) NOT NULL,
                        `phone` varchar(15) DEFAULT NULL,
                        `password` varchar(70) NOT NULL,
                        `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `username` (`username`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->pdo->exec($SQL);
    }

    public function down()
    {
        // Query to drop migration table created
        $db = Application::$app->db;
        $SQL = "DROP TABLE IF EXISTS users;";
        $db->pdo->exec($SQL);
    }
}
