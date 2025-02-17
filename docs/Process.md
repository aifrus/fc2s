# Process Class

The `Process` class in the Aifrus FC2S library provides a comprehensive workflow for handling FAA data. It automates the process of downloading, extracting, processing, and exporting datasets.

## Configuration

To use the `Process` class, you need to prepare a configuration array with the following keys:

- `export_dir`: Directory where the exported data will be saved.
- `host`: Database host.
- `user`: Database user.
- `pass`: Database password.
- `prefix`: Prefix for the dataset.

## Methods

### get_latest

Processes the latest available FAA data.

```php
use Aifrus\Fc2s\Process;
Process::get_latest($config);
```

### get_current

Processes the current FAA data.

```php
Process::get_current($config);
```

### get_all

Processes all available FAA data.

```php
Process::get_all($config);
```

## Workflow

1. **Download Data**: Fetches the latest or current dataset from the FAA.
2. **Extract Data**: Unzips the downloaded data for processing.
3. **Generate Schema**: Uses the `Schema` class to create SQL statements.
4. **Create Database**: Sets up a new database for the dataset.
5. **Execute SQL**: Runs the generated SQL statements to populate the database.
6. **Export Database**: Dumps the database to a file and compresses it.

## Conclusion

The `Process` class is designed to streamline the handling of FAA data, making it easy to manage datasets with minimal manual intervention.