# Aifrus FC2S Library

The Aifrus FC2S library provides tools for processing FAA data, including downloading, extracting, and managing datasets. This guide will help developers understand how to use the library, either by utilizing the prebuilt process or by using individual functions.

## Prebuilt Process

The library offers a streamlined process for handling FAA data, which can be executed using the `Process` class. This class manages the entire workflow, from downloading data to exporting it.

### Usage

1. **Configuration**: Prepare a configuration array with the following keys:
   - `export_dir`: Directory where the exported data will be saved.
   - `host`: Database host.
   - `user`: Database user.
   - `pass`: Database password.
   - `prefix`: Prefix for the dataset.

2. **Process Data**:
   - To process the latest data:
     ```php
     use Aifrus\Fc2s\Process;
     Process::getLatest($config);
     ```
   - To process the current data:
     ```php
     Process::getCurrent($config);
     ```
   - To process all available data:
     ```php
     Process::getAll($config);
     ```

## Alacarte Usage

Developers can also use individual functions from the library for more granular control.

### Fetching Data

- **FetchFAA**: Use this class to retrieve FAA data URLs and available dates.
  ```php
  use Aifrus\Fc2s\FetchFAA;
  $dates = FetchFAA::getAvailableDates();
  $url = FetchFAA::getDataFileUrl($dates[0]);
  ```

### Handling HTTP Requests

- **HTTPS**: Send HTTP requests or download files.
  ```php
  use Aifrus\Fc2s\HTTPS;
  $html = HTTPS::get($url);
  HTTPS::download($url, '/path/to/save.zip');
  ```

### Processing Data

- **Schema**: Generate SQL statements from FAA data.
  ```php
  use Aifrus\Fc2s\Schema;
  $statements = Schema::generate('/path/to/data');
  ```

### Managing Zip Files

- **Zip**: Extract or create zip archives.
  ```php
  use Aifrus\Fc2s\Zip;
  Zip::extract('/path/to/archive.zip', '/extract/path');
  Zip::create('/path/to/new.zip', ['/file1', '/file2']);
  ```

## Conclusion

The Aifrus FC2S library is designed to simplify the process of handling FAA data. Whether using the prebuilt process or individual functions, developers can efficiently manage and process datasets.