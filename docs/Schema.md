# Schema Class

The `Schema` class is responsible for generating SQL statements from FAA data, enabling the creation of database tables and the insertion of data.

## Methods

### generate

Generates SQL statements from schema files in a directory.

```php
use Aifrus\Fc2s\Schema;
$statements = Schema::generate('/path/to/data');
```

- **Parameters**:
  - `$data_dir`: The directory containing schema files.

### getSchemaFiles

Retrieves schema files from a directory.

```php
$files = Schema::getSchemaFiles('/path/to/data');
```

- **Parameters**:
  - `$data_dir`: The directory to search for schema files.

### processSchemaFiles

Processes schema files to generate SQL statements.

```php
$statements = Schema::processSchemaFiles($files);
```

- **Parameters**:
  - `$schema_files`: An array of schema file paths.

### processSchemaFile

Processes a single schema file to generate SQL statements.

```php
$statements = Schema::processSchemaFile('/path/to/schema.csv');
```

- **Parameters**:
  - `$schema_file`: The path to the schema file.

## Usage

The `Schema` class is crucial for transforming FAA data into SQL statements, which can then be executed to populate databases with the necessary tables and data.