<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\Exceptions\{
    CurlException,
    ProcessException,
    DirectoryCreationException,
    FileWriteException,
    SqlException,
    ZipException,
    SchemaException
};

use mysqli;

/**
 * Class Process
 * Manages the processing of FAA data including downloading, database creation, and exporting.
 */
class Process
{
    const INDEX_DB = 'INDEX';
    const INDEX_TABLE = 'INDEX';
    private ?mysqli $sql = null;
    private ?string $prefix = null;
    private ?string $export_dir = null;

    /**
     * Process constructor.
     * Initializes the process with configuration and sets up the database connection.
     *
     * @param array $config Configuration array containing database and export directory settings.
     * @throws ProcessException If required configuration is missing.
     * @throws DirectoryCreationException If the export directory cannot be created.
     * @throws SqlException If the database connection fails.
     */
    public function __construct(private array $config)
    {
        $this->export_dir = $config['export_dir'] ?? null;
        if (!$this->export_dir) throw new ProcessException("Missing export directory.");
        if (!is_dir($this->export_dir)) {
            echo ("Creating export directory...\n");
            if (!mkdir($this->export_dir, 0777, true)) throw new DirectoryCreationException("Failed to create export directory.");
        }

        $host = $config['host'] ?? null;
        $user = $config['user'] ?? null;
        $pass = $config['pass'] ?? null;
        if (!$host || !$user || !$pass) throw new ProcessException("Missing database credentials.");

        echo ("Connecting to database...\n");
        $this->sql = new mysqli($host, $user, $pass);
        if ($this->sql->connect_error) throw new SqlException("Failed to connect to database: " . $this->sql->connect_error);

        $this->prefix = ($config['prefix'] ?? null) or throw new ProcessException("Missing dataset prefix.");
        echo ("Creating index database...\n");
        $this->createIndexDatabase() or throw new SqlException("Failed to create index database.");
    }

    /**
     * Processes the latest available FAA data.
     *
     * @param array $config Configuration array.
     * @return bool True on success, false on failure.
     */
    public static function getLatest(array $config): bool
    {
        return (new self($config))->processLatest();
    }

    /**
     * Processes the current FAA data.
     *
     * @param array $config Configuration array.
     * @return bool True on success, false on failure.
     */
    public static function getCurrent(array $config): bool
    {
        return (new self($config))->processCurrent();
    }

    /**
     * Processes all available FAA data.
     *
     * @param array $config Configuration array.
     * @return bool True on success, false on failure.
     */
    public static function getAll(array $config): bool
    {
        return (new self($config))->processAllAvailable();
    }

    /**
     * Processes the latest dataset.
     *
     * @return bool True on success, false on failure.
     * @throws CurlException If fetching the dataset date fails.
     */
    public function processLatest(): bool
    {
        echo ("Fetching latest date...\n");
        $date = FetchFAA::getAvailableDates()[0] or throw new CurlException("Failed to get current dataset date.");
        return $this->processDate($date);
    }

    /**
     * Processes the current dataset.
     *
     * @return bool True on success, false on failure.
     * @throws CurlException If fetching the current dataset date fails.
     */
    public function processCurrent(): bool
    {
        echo ("Fetching current date...\n");
        $date = FetchFAA::getCurrentDate() or throw new CurlException("Failed to get current dataset date.");
        return $this->processDate($date);
    }

    /**
     * Processes all available datasets.
     *
     * @return bool True on success, false on failure.
     * @throws CurlException If fetching available dataset dates fails.
     */
    public function processAllAvailable(): bool
    {
        $success = 0;
        $error = 0;
        echo ("Fetching available dates...\n");
        $dates = FetchFAA::getAvailableDates() or throw new CurlException("Failed to get available dataset dates.");
        foreach (array_reverse($dates) as $date) {
            $res = $this->processDate($date);
            if ($res) $success++;
            else $error++;
        }
        return !$error;
    }

    /**
     * Processes a dataset for a specific date.
     *
     * @param string $date The date of the dataset to process.
     * @return bool True on success, false on failure.
     */
    public function processDate(string $date): bool
    {
        echo ("Processing $date...\n");
        $error = false;
        echo ("Making tmp folder...\n");
        $tmp_dir = $this->makeTmpFolder() or throw new DirectoryCreationException("Failed to create temporary directory.");
        try {
            echo ("Fetching data file URL...\n");
            $url = FetchFAA::getDataFileUrl($date) or throw new CurlException("Failed to get dataset URL.");
            echo ("Downloading dataset...\n");
            $zip = $tmp_dir . '/' . basename($url) or throw new FileWriteException("Failed to get dataset ZIP path.");
            HTTPS::download($url, $zip, FetchFAA::HEADERS) or throw new CurlException("Failed to download dataset.");
            echo ("Extracting dataset...\n");
            Zip::extract($zip, $tmp_dir) or throw new ZipException("Failed to extract dataset.");
            echo ("Setting permissions...\n");
            $this->setPermissions($tmp_dir) or throw new DirectoryCreationException("Failed to set permissions.");
            echo ("Generating schema...\n");
            $statements = Schema::generate($tmp_dir) or throw new SchemaException("Failed to generate schema.");
            echo ("Creating database...\n");
            $db_name = $this->createDatabase($date) or throw new SqlException("Failed to create database.");
            echo ("Executing statements...\n");
            $this->executeStatements($db_name, $statements) or throw new SqlException("Failed to execute statements.");
            echo ("Exporting database...\n");
            $this->exportDatabase($db_name) or throw new ProcessException("Failed to export database.");
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";
            $error = true;
        }
        echo ("Cleaning up...\n");
        $this->deleteDirectory($tmp_dir) or throw new DirectoryCreationException("Failed to delete temporary directory.");
        return !$error;
    }

    /**
     * Executes SQL statements on a specified database.
     *
     * @param string $db_name The name of the database.
     * @param array $statements The SQL statements to execute.
     * @return bool True on success, false on failure.
     * @throws SqlException If executing a statement fails.
     */
    public function executeStatements(string $db_name, array $statements): bool
    {
        $this->sql->select_db($db_name);
        foreach ($statements as $statement) {
            if (!$statement) continue;
            if ($this->sql->multi_query($statement)) {
                do {
                    // free result
                    if ($result = $this->sql->store_result()) {
                        $result->free();
                    }
                    // check if there are more query results from a previous call to mysqli::multi_query()
                } while ($this->sql->more_results() && $this->sql->next_result());
            }

            if ($this->sql->errno) {
                throw new SqlException("Failed to execute statement: " . $this->sql->error);
            }
        }
        return true;
    }

    /**
     * Creates a temporary folder for processing.
     *
     * @return string The path to the created temporary folder.
     * @throws DirectoryCreationException If the directory cannot be created.
     */
    public function makeTmpFolder(): string
    {
        $tmp_dir = sys_get_temp_dir() . '/fc2s_' . time() . '_' . rand(10000000, 99999999);
        if (!mkdir($tmp_dir)) throw new DirectoryCreationException("Failed to create temporary directory.");
        return $tmp_dir;
    }

    /**
     * Sets file permissions for a directory.
     *
     * @param string $directory The directory to set permissions for.
     * @return bool True on success, false on failure.
     * @throws DirectoryCreationException If setting permissions fails.
     */
    public function setPermissions(string $directory): bool
    {
        foreach (scandir($directory) as $file) if ($file != '.' && $file != '..') chmod($directory . '/' . $file, 0644) or throw new DirectoryCreationException("Failed to set permissions.");
        return true;
    }

    /**
     * Creates a database for a specific date.
     *
     * @param string $date The date for which to create the database.
     * @return string The name of the created database.
     * @throws SqlException If creating the database fails.
     */
    public function createDatabase(string $date): string
    {
        $db_name = $this->prefix . $date;
        $this->sql->query("DROP DATABASE IF EXISTS `$db_name`");
        if ($this->sql->error) throw new SqlException("Failed to drop database: " . $this->sql->error);
        $this->sql->query("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        if ($this->sql->error) throw new SqlException("Failed to create database: " . $this->sql->error);
        $preview = strtotime($date) > time() ? 1 : 0;
        $query = "INSERT INTO `" . $this->prefix . self::INDEX_DB . "`.`" . self::INDEX_TABLE . "` (`name`, `preview`) VALUES ('$db_name', $preview) ON DUPLICATE KEY UPDATE `preview` = $preview";
        $this->sql->query($query);
        if ($this->sql->error) throw new SqlException("Failed to insert dataset: " . $this->sql->error);
        return $db_name;
    }

    /**
     * Creates the index database and table if they do not exist.
     *
     * @return bool True on success, false on failure.
     * @throws SqlException If creating the index database or table fails.
     */
    public function createIndexDatabase(): bool
    {
        $name_len = strlen($this->prefix) + 10;
        $index_db = $this->prefix . self::INDEX_DB;
        $query = "CREATE DATABASE IF NOT EXISTS `$index_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
        $this->sql->query($query);
        if ($this->sql->error) throw new SqlException("Failed to create index database: " . $this->sql->error);
        $this->sql->select_db($index_db);
        $query = "CREATE TABLE IF NOT EXISTS `" . self::INDEX_TABLE . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `imported` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `name` varchar({$name_len}) NOT NULL,
                `preview` tinyint(1) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->sql->query($query);
        if ($this->sql->error) throw new SqlException("Failed to create datasets table: " . $this->sql->error);
        return true;
    }

    /**
     * Exports a database to a SQL file and compresses it.
     *
     * @param string $db_name The name of the database to export.
     * @return bool True on success, false on failure.
     * @throws ProcessException If exporting the database fails.
     * @throws ZipException If creating the zip file fails.
     * @throws FileWriteException If deleting the SQL file fails.
     */
    public function exportDatabase(string $db_name): bool
    {
        $sql_file = $this->export_dir . '/' . $db_name . '.sql';
        $zip_file = $sql_file . '.zip';
        $command = "mysqldump --compatible=ansi --skip-comments -u {$this->config['user']} -p{$this->config['pass']} -h {$this->config['host']} $db_name > $sql_file";
        exec($command, $output, $return_var);
        if ($return_var !== 0) throw new ProcessException("Failed to export database: " . implode("\n", $output));
        Zip::create($zip_file, [$sql_file]) or throw new ZipException("Failed to create zip file.");
        unlink($sql_file) or throw new FileWriteException("Failed to delete SQL file.");
        return true;
    }

    /**
     * Deletes a directory and its contents.
     *
     * @param string $dir The directory to delete.
     * @return bool True on success, false on failure.
     * @throws DirectoryCreationException If deleting the directory fails.
     * @throws FileWriteException If deleting a file fails.
     */
    public function deleteDirectory(string $dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) $this->deleteDirectory($path);
            else unlink($path) or throw new FileWriteException("Failed to delete file: $path");
        }
        return rmdir($dir) or throw new DirectoryCreationException("Failed to delete directory: $dir");
    }
}
