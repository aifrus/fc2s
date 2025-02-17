<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\Exceptions\{
    DirectoryNotFoundException,
    FileNotFoundException,
    SchemaException
};

/**
 * Schema class for generating SQL statements from FAA data.
 */
class Schema
{
    /**
     * Generates SQL statements from schema files in a directory.
     *
     * @param string $data_dir The directory containing schema files.
     * @return array|false An array of SQL statements or false on failure.
     */
    public static function generate(string $data_dir): array|false
    {
        return self::processSchemaFiles(self::getSchemaFiles($data_dir));
    }

    /**
     * Retrieves schema files from a directory.
     *
     * @param string $data_dir The directory to search for schema files.
     * @return array An array of schema file paths.
     * @throws DirectoryNotFoundException If the directory does not exist.
     * @throws FileNotFoundException If no schema files are found.
     */
    public static function getSchemaFiles(string $data_dir): array
    {
        if (!is_dir($data_dir)) throw new DirectoryNotFoundException("Directory not found: {$data_dir}");
        $files = glob($data_dir . '/*_CSV_DATA_STRUCTURE.csv');
        if (!$files) throw new FileNotFoundException("No schema files found in {$data_dir}.");
        return $files;
    }

    /**
     * Processes schema files to generate SQL statements.
     *
     * @param array $schema_files An array of schema file paths.
     * @return array An array of SQL statements.
     */
    public static function processSchemaFiles(array $schema_files): array
    {
        $statements = [];

        foreach ($schema_files as $schema_file) {
            $statements = array_merge($statements, self::processSchemaFile($schema_file));
        }

        return $statements;
    }

    /**
     * Processes a single schema file to generate SQL statements.
     *
     * @param string $schema_file The path to the schema file.
     * @return array An array of SQL statements.
     * @throws FileNotFoundException If the file cannot be opened.
     * @throws SchemaException If an unexpected data type is encountered.
     */
    public static function processSchemaFile(string $schema_file): array
    {
        $handle = fopen($schema_file, 'r');
        if (!$handle) throw new FileNotFoundException("File not found: {$schema_file}");

        $tableName = '';
        $columns = [];
        $statements = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($data[0] !== 'CSV File') {
                if ($tableName !== $data[0]) {
                    // New table, generate SQL statement for previous table
                    if (!empty($columns)) {
                        $statements[] = self::generateSql($tableName, $columns, $schema_file);
                    }

                    // Reset for new table
                    $tableName = $data[0];
                    $columns = [];
                }

                $mappedType = self::mapDataType($data[3], $data[2]);
                if ($mappedType === false) {
                    throw new SchemaException("Unexpected data type: {$data[3]} in {$schema_file}");
                }

                $columns[] = [
                    'name' => $data[1],
                    'type' => $mappedType,
                    'nullable' => $data[4] === 'Yes'
                ];
            }
        }

        // Generate SQL statement for the last table
        if (!empty($columns)) {
            $statements[] = self::generateSql($tableName, $columns, $schema_file);
        }

        fclose($handle);

        return $statements;
    }

    /**
     * Generates an SQL statement for a table.
     *
     * @param string $tableName The name of the table.
     * @param array $columns An array of column definitions.
     * @param string $schema_file The path to the schema file.
     * @return string The generated SQL statement.
     */
    private static function generateSql(string $tableName, array $columns, string $schema_file): string
    {
        $schema_path = dirname($schema_file);
        $table_csv = "{$schema_path}/{$tableName}.csv";

        if (!file_exists($table_csv)) {
            echo ("Warning: Missing table CSV file: {$table_csv}\n");
            return '';
        }

        $sql = "CREATE TABLE `{$tableName}` (";
        $columnDefs = [];

        foreach ($columns as $column) {
            $columnDefs[] = '`' . trim($column['name']) . "` {$column['type']}" . ($column['nullable'] ? '' : ' NOT NULL');
        }

        $sql .= implode(', ', $columnDefs);
        $sql .= ");\n";

        // Add BULK INSERT statement
        $sql .= "LOAD DATA LOCAL INFILE '{$table_csv}' INTO TABLE `{$tableName}` FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;";

        return $sql;
    }

    /**
     * Maps a data type from the schema to an SQL data type.
     *
     * @param string $type The data type from the schema.
     * @param string $maxLength The maximum length or precision of the data type.
     * @return string|false The mapped SQL data type or false if mapping fails.
     */
    private static function mapDataType(string $type, string $maxLength): string|false
    {
        if ($type === 'NUMBER') {
            // Extract precision and scale from maxLength
            preg_match('/\((\d+),(\d+)\)/', $maxLength, $matches);
            $precision = $matches[1];
            $scale = $matches[2];

            if ($scale > 0) {
                return "DECIMAL({$precision},{$scale})";
            } else {
                return "INT({$precision})";
            }
        } elseif ($type === 'VARCHAR') {
            // Extract length from maxLength
            $length = intval($maxLength);

            return "VARCHAR({$length})";
        }
        return false;
    }
}
