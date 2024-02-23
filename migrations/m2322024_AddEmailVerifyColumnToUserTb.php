
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

            use  app\core\Application;
            class m2322024_AddEmailVerifyColumnToUserTb
            {
                public function up()
                {
                    $db = Application::$app->db;
                    // Query to create and alter goes here
                    $SQL = "ALTER TABLE users ADD COLUMN email_verified int DEFAULT '0' AFTER email";
                    $db->pdo->exec($SQL);
                }

                public function down()
                {
                    // Query to drop migration table created
                    $db = Application::$app->db;
                    $SQL = "DROP TABLE users;";
                    $db->pdo->exec($SQL);
                }
            }
        