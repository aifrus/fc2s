# Zip Class

The `Zip` class provides methods for handling zip archives, including extraction and creation, which are essential for managing FAA data files.

## Methods

### extract

Extracts all files from a zip archive to a specified path.

```php
use Aifrus\Fc2s\Zip;
Zip::extract('/path/to/archive.zip', '/extract/path');
```

- **Parameters**:
  - `$zip_path`: Path to the zip file.
  - `$extract_path`: Path to extract the files to.

### create

Creates a zip archive from a list of files.

```php
Zip::create('/path/to/new.zip', ['/file1', '/file2']);
```

- **Parameters**:
  - `$zip_path`: Path to the zip file to create.
  - `$files`: An array of file paths to include in the zip archive.

## Usage

The `Zip` class is used to manage zip files, which is particularly useful when dealing with FAA datasets that are distributed as zip archives.