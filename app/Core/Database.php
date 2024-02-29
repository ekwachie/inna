<?php

/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * try {
 *    $db = new Database($db);
 *    $db->select("SELECT * FROM user WHERE id = :id", array('id', 25));
 *    $db->insert("user", array('name' => 'jesse'));
 *    $db->update("user", array('name' => 'juicy), "id = '25'");
 *    $db->delete("user", "id = '25'");
 * } catch (Exception $e) {
 *    echo $e->getMessage();
 * }
 */

namespace app\Core;

use app\Core\Utils\DUtil;
use \PDO as PDO;

class Database
{
    public $pdo;
    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * apply migrations
     */
    public function applyMigrations()
    {
        //  if migration directory exist - else create migrations dir
        DUtil::isDir("migrations");
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];

        /**
         * List files and directories inside the specified path Returns an array of files and directories from the directory.
         *Returns:
         *Returns an array of filenames on success, or false on failure. If directory is not a directory, then boolean false is returned, and an error of level E_WARNING is generated.
         */
        $files = scandir(Application::$ROOT_DIR . '/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations);

        foreach ($toApplyMigrations as $migration) {
            if ($migration == '.' || $migration == '..') {
                continue;
            }
            require_once Application::$ROOT_DIR . '/migrations/' . $migration;
            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Applying migration $migration");
            try {
                $instance->up();
                $this->log("\033[38;2;0;102;0m Applied migration $migration");
                $newMigrations[] = $migration;
            } catch (\Throwable $th) {
                $this->log("\033[38;2;255;0;0m Something went wrong. check your query $migration");
                exit(0);
            }
        }
        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("\033[38;2;0;102;0m All migrations are apllied");
        }
    }

    public function createMigrationsTable()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=INNODB;");
    }

    public function getAppliedMigrations()
    {
        $stmt = $this->pdo->prepare("SELECT migration FROM migrations");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveMigrations(array $migrations)
    {
        $str = implode(",", array_map(fn($m) => "('$m')", $migrations));
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES $str");
        $stmt->execute();
    }

    protected function log($msg)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $msg . PHP_EOL;
    }

    //  if migration directory exist - else create migrations dir
    // protected function isDir()
    // {
    //     if (is_dir("./migrations")) {
    //     } else {
    //         $this->log("Creating migration directory ...");
    //         mkdir("./migrations");
    //         $this->log("\033[38;2;0;102;0m Migration directory created successfully");
    //     }
    // }

    // create migration
    public function Add($migration_name)
    {
        //check if migration dir exist
        DUtil::isDir("migrations");

        $script = '
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
         * If you create a table with a name that already exists in the database, you\'ll get an error. To avoid the error, you can use the IF NOT EXISTS option.
         * 
         * You can use the keyword FIRST if you want the new column to be positioned as the first column in the table. Alternatively, you can use the AFTER existing_column clause to specify that you want to add a new column after an existing column.
         * // Query to ALTER TABLE  "ALTER TABLE table_name ADD COLUMN new_column_name data_type [FIRST | AFTER existing_column];"
         * 
         */

            use  app\core\Application;
            class m' . date("jnY") . '_' . $migration_name . '
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
        ';

        $this->creatMigration($script, $migration_name);

    }

    public function creatMigration($script, $migration_name)
    {
        if (file_exists("./migrations/"."m". date("jnY") . "_".$migration_name. '.php')) {
            $this->log("\033[38;2;255;0;0m $migration_name migration exist");
        } else {
            $this->log("Creating migration $migration_name");
            file_put_contents("./migrations/m" . date("jnY") . "_" . $migration_name . '.php', $script, FILE_APPEND);
            $this->log("\033[38;2;0;102;0m Created migration $migration_name successfully");
        }
    }
    
    // run migrations
    public function startMigration($action, $migration_name)
    {
        if (!empty($action) || !empty($migration_name)) {
            switch ($action) {
                case 'add':
                    if (!empty($migration_name)) {
                        $this->Add($migration_name);
                    } else {
                        echo "\033[38;2;255;0;0m Migration name key not set";
                    }
                    break;
                case 'update':
                    $this->applyMigrations();
                    break;

                default:
                    echo "\033[38;2;255;0;0m check command format " . $action;
                    break;
            }
        } else {
            echo "\033[38;2;255;0;0m Migration action or name key not set";
        }
    }
}
