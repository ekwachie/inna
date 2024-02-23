
        <?php
        /**
         * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
         * @copyright   Copyright (C), 2019 Evans Kwachie.
         * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
         *              Refer to the LICENSE file distributed within the package.
         *
         * @todo PDO exception and error handling
         * @category    Migrations
         * table_name: This is the name of the table that you want to create.
         * column1, column2, etc.: The names of the columns in the table.
         * datatype: the data of each column such as INT, VARCHAR, DATE, etc.
         * constraints: These are optional constraints such as NOT NULL, UNIQUE, PRIMARY KEY, and FOREIGN KEY.
         * If you create a table with a name that already exists in the database, you'll get an error. To avoid the error, you can use the IF NOT EXISTS option.
         * 
         * You can use the keyword FIRST if you want the new column to be positioned as the first column in the table. Alternatively, you can use the AFTER existing_column clause to specify that you want to add a new column after an existing column.
         * // Query to ALTER TABLE  "ALTER TABLE table_name ADD COLUMN new_column_name data_type [FIRST | AFTER existing_column];"
         * 
         */

            use  app\core\Application;
            class m2322024_CreateUserRolesTb
            {
                // for applying migrations
                public function up()
                {
                    $db = Application::$app->db;

                    // Query to CREATE TABLE 
                    $createTb = "CREATE TABLE IF NOT EXISTS table_name(
                                column1 datatype constraints,
                                column1 datatype constraints,
                            ) ENGINE=storage_engine;";
                    
                    $db->pdo->exec($createTb);
                }

                // for dropping table
                public function down()
                {
                    // Query to drop migration table created
                    $db = Application::$app->db;
                    $SQL = "DROP TABLE IF EXISTS [table]";
                    $db->pdo->exec($SQL);
                }
            }
        