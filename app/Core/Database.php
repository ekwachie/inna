<?php

namespace app\Core;

use app\Core\Utils\DUtil;
use \PDO as PDO;

class Database extends DbModel
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
     * Apply database migrations
     */
    public function applyMigrations()
    {
        $directory = Application::$ROOT_DIR . '/migration';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('Cannot create migration directory.');
    
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();
        $newMigrations = [];
    
        $files = array_filter(scandir(Application::$ROOT_DIR . '/migration'), function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });
        
        // Sort migrations to ensure proper order
        sort($files);
    
        $toApplyMigrations = array_diff($files, $appliedMigrations);
    
        foreach ($toApplyMigrations as $migration) {
            require_once Application::$ROOT_DIR . '/migration/' . $migration;
    
            // Ensure correct class name (without .php)
            $className = "app\\migrations\\" . pathinfo($migration, PATHINFO_FILENAME);
    
            if (!class_exists($className)) {
                throw new \RuntimeException('Migration class not found: ' . $className);
            }
    
            $instance = new $className();
            $this->log("Applying migration $migration");
    
            try {
                $instance->up();
                $this->saveMigrations([$migration]);
                $this->log("\033[38;2;0;102;0m Applied migration $migration");
                $newMigrations[] = $migration;
            } catch (\Throwable $th) {
                throw new \RuntimeException('Migration failed: ' . $migration, 0, $th);
            }
        }
    
        if (empty($newMigrations)) {
            $this->log("\033[38;2;0;102;0m All migrations are applied");
        }
    }
    

    public function createMigrationsTable()
    {
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration VARCHAR(255) UNIQUE, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
            return;
        }
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
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        foreach ($migrations as $migration) {
            $stmt->execute([$migration]);
        }
    }

    protected function log($msg)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $msg . PHP_EOL;
    }

    public function addMigration($migrationName, $columns)
{
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', $migrationName) || !$columns) throw new \InvalidArgumentException('Use a safe table name and at least one column.');
    foreach (array_keys($columns) as $column) if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', $column)) throw new \InvalidArgumentException('Invalid column name.');
    $directory = Application::$ROOT_DIR . '/migration';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('Cannot create migration directory.');
    
    $className = 'm' . date('YmdHis') . '_' . $migrationName;
    $fileName = $className . '.php';
    $filePath = $directory . '/' . $fileName;

    if (file_exists($filePath)) {
        $this->log("\033[38;2;255;0;0m $migrationName migration already exists");
        return;
    }

    // Generate column definitions
    $columnDefinitions = implode(",\n    ", array_map(
        fn($col, $type) => "$col $type",
        array_keys($columns),
        array_values($columns)
    ));

    // Generate the migration script
    $script = "<?php\n\nnamespace app\\migrations;\n\nuse app\\Core\\Application;\n\nclass $className\n{\n    public function up()\n    {\n        \$db = Application::\$app->db;\n        \$db->pdo->exec(\"CREATE TABLE IF NOT EXISTS $migrationName (\n    $columnDefinitions\n) ENGINE=INNODB;\");\n    }\n    \n    public function down()\n    {\n        \$db = Application::\$app->db;\n        \$db->pdo->exec(\"DROP TABLE IF EXISTS $migrationName;\");\n    }\n}\n";

    if (file_put_contents($filePath, $script, LOCK_EX) === false) throw new \RuntimeException('Cannot write migration file.');
    $this->log("\033[38;2;0;102;0m Created migration $migrationName successfully");
}


    public function startMigration($action, $migrationName, $columns = [])
{
    if (!$action) {
        echo "\033[38;2;255;0;0m Migration not set.\n";
        return;
    }

    switch ($action) {
        case 'add':
            if (!empty($columns)) {
                $this->addMigration($migrationName, $columns);
            } else {
                echo "\033[38;2;255;0;0m Usage:  ./migrations add <table_name> <column:type> <column:type> ...\n";
            }
            break;
        case 'update':
            $this->applyMigrations();
            break;
        default:
            echo "\033[38;2;255;0;0m Invalid command format: $action.\n";
            break;
    }
}

}
