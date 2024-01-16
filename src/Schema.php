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
    public static function generate(string $data_dir): array|false
    {
        return self::process_schema_files(self::get_schema_files($data_dir));
    }

    public static function get_schema_files(string $data_dir): array
    {
        if (!is_dir($data_dir)) throw new DirectoryNotFoundException("Directory not found: {$data_dir}");
        $files = glob($data_dir . '/*_CSV_DATA_STRUCTURE.csv');
        if (!$files) throw new FileNotFoundException("No schema files found in {$data_dir}.");
        return $files;
    }

    public static function process_schema_files(array $schema_files): array
    {
        $statements = [];

        foreach ($schema_files as $schema_file) {
            $statements = array_merge($statements, self::process_schema_file($schema_file));
        }

        return $statements;
    }

    public static function process_schema_file(string $schema_file): array
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
                        $statements[] = self::generate_sql($tableName, $columns, $schema_file);
                    }

                    // Reset for new table
                    $tableName = $data[0];
                    $columns = [];
                }

                $mappedType = self::map_data_type($data[3], $data[2]);
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
            $statements[] = self::generate_sql($tableName, $columns, $schema_file);
        }

        fclose($handle);

        return $statements;
    }

    private static function generate_sql(string $tableName, array $columns, string $schema_file): string
    {
        $schema_path = dirname($schema_file);
        $table_csv = "{$schema_path}/{$tableName}.csv";
        $sql = "CREATE TABLE `{$tableName}` (";
        $columnDefs = [];

        foreach ($columns as $column) {
            $columnDefs[] = '`' . trim($column['name']) . "` {$column['type']}" . ($column['nullable'] ? '' : ' NOT NULL');
        }

        $sql .= implode(', ', $columnDefs);
        $sql .= ");";

        // Add BULK INSERT statement
        $sql .= "BULK INSERT `{$tableName}` FROM '{$table_csv}' WITH (FORMAT = 'CSV', FIRSTROW = 2);";

        return $sql;
    }

    private static function map_data_type(string $type, string $maxLength): string|false
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
