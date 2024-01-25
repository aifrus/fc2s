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

class Process
{
    const INDEX_DB = 'INDEX';
    const INDEX_TABLE = 'INDEX';
    private ?mysqli $sql = null;
    private ?string $prefix = null;
    private ?string $export_dir = null;

    public function __construct(private array $config)
    {
        $this->export_dir = $config['export_dir'] ?? null;
        if (!$this->export_dir) throw new ProcessException("Missing export directory.");
        if (!is_dir($this->export_dir)) {
            if (!mkdir($this->export_dir, 0777, true)) throw new DirectoryCreationException("Failed to create export directory.");
        }

        $host = $config['host'] ?? null;
        $user = $config['user'] ?? null;
        $pass = $config['pass'] ?? null;
        if (!$host || !$user || !$pass) throw new ProcessException("Missing database credentials.");

        $this->sql = new mysqli($host, $user, $pass);
        if ($this->sql->connect_error) throw new SqlException("Failed to connect to database: " . $this->sql->connect_error);

        $this->prefix = ($config['prefix'] ?? null) or throw new ProcessException("Missing dataset prefix.");
        $this->create_index_database() or throw new SqlException("Failed to create index database.");
    }

    public static function get_latest(array $config): bool
    {
        return (new self($config))->process_latest();
    }

    public static function get_all(array $config): bool
    {
        return (new self($config))->process_all_available();
    }

    public function process_latest(): bool
    {
        $date = FetchFAA::get_available_dates()[0] or throw new CurlException("Failed to get current dataset date.");
        return $this->process_date($date);
    }

    public function process_current(): bool
    {
        $date = FetchFAA::get_current_date() or throw new CurlException("Failed to get current dataset date.");
        return $this->process_date($date);
    }

    public function process_all_available(): bool
    {
        $success = 0;
        $error = 0;
        $dates = FetchFAA::get_available_dates() or throw new CurlException("Failed to get available dataset dates.");
        foreach (array_reverse($dates) as $date) {
            $res = $this->process_date($date);
            if ($res) $success++;
            else $error++;
        }
        return !$error;
    }

    public function process_date(string $date): bool
    {
        echo ("Processing $date\n");
        $error = false;
        $tmp_dir = $this->make_tmp_folder() or throw new DirectoryCreationException("Failed to create temporary directory.");
        try {
            $url = FetchFAA::get_data_file_url($date) or throw new CurlException("Failed to get dataset URL.");
            $zip = $tmp_dir . '/' . basename($url) or throw new FileWriteException("Failed to get dataset ZIP path.");
            HTTPS::download($url, $zip, FetchFAA::HEADERS) or throw new CurlException("Failed to download dataset.");
            Zip::extract($zip, $tmp_dir) or throw new ZipException("Failed to extract dataset.");
            $this->set_permissions($tmp_dir) or throw new DirectoryCreationException("Failed to set permissions.");
            $statements = Schema::generate($tmp_dir) or throw new SchemaException("Failed to generate schema.");
            $db_name = $this->create_database($date) or throw new SqlException("Failed to create database.");
            $this->execute_statements($db_name, $statements) or throw new SqlException("Failed to execute statements.");
            $this->export_database($db_name) or throw new ProcessException("Failed to export database.");
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n";
            $error = true;
        }
        $this->delete_directory($tmp_dir) or throw new DirectoryCreationException("Failed to delete temporary directory.");
        return !$error;
    }

    public function execute_statements(string $db_name, array $statements): bool
    {
        $this->sql->select_db($db_name);
        foreach ($statements as $statement) {
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

    public function make_tmp_folder(): string
    {
        $tmp_dir = sys_get_temp_dir() . '/fc2s_' . time() . '_' . rand(10000000, 99999999);
        if (!mkdir($tmp_dir)) throw new DirectoryCreationException("Failed to create temporary directory.");
        return $tmp_dir;
    }

    public function set_permissions(string $directory): bool
    {
        foreach (scandir($directory) as $file) if ($file != '.' && $file != '..') chmod($directory . '/' . $file, 0644) or throw new DirectoryCreationException("Failed to set permissions.");
        return true;
    }

    public function create_database(string $date): string
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

    public function create_index_database(): bool
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

    public function export_database(string $db_name): bool
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

    public function delete_directory(string $dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) $this->delete_directory($path);
            else unlink($path) or throw new FileWriteException("Failed to delete file: $path");
        }
        return rmdir($dir) or throw new DirectoryCreationException("Failed to delete directory: $dir");
    }
}
