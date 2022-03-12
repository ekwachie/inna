<?php
/**
 * @author      Desmond Evans Kwachie Jr <iamdesmondjr@gmail.com>
 * @copyright   Copyright (C), 2019 Desmond Evans Kwachie Jr.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */
use  app\core\Application;

class m0001_initial
{
    public function up()
    {
        $db = Application::$app->db;
        $SQL = "CREATE TABLE `users` (
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
        $db = Application::$app->db;
        $SQL = "DROP TABLE users;";
        $db->pdo->exec($SQL);
    }
}
