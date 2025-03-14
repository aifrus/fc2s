# FAA CSV to SQL (FC2S)

This project, FC2S (FAA CSV to SQL), is designed to convert Federal Aviation Administration (FAA) data from Comma Separated Values (CSV) format to Structured Query Language (SQL). The project intends to download the latest aeronautical data published by the FAA on a 28-day cycle, which they publish in CSV format. The process includes downloading the data, unzipping it, creating a new database, creating the tables (based on the current schema provided by the FAA with the download), importing the data, and then exporting the data as one large .sql file with all the tables. The final step is to zip them up and make them available to anyone who wants the data in SQL format.

FMI: https://www.faa.gov/air_traffic/flight_info/aeronav/aero_data/NASR_Subscription/

If you are only looking for the final data in SQL format you can download every available cycle from our repository:

https://github.com/aifrus/nasr_sql_zips

## Acronyms

- **FAA**: Federal Aviation Administration
- **CSV**: Comma Separated Values
- **SQL**: Structured Query Language
- **FC2S**: FAA CSV to SQL

## Process

1. Download the latest aeronautical data published by the FAA in CSV format.
2. Unzip the downloaded data.
3. Create a new database.
4. Create tables based on the current schema the FAA provides with the download.
5. Import the data into the newly created tables.
6. Export the data as one large .sql file with all the tables.
7. Zip the .sql file.

## Installation

To install the package, run the following command:

```bash
composer require aifrus/fc2s
```

## Usage Example

Below is an example of how to use the package to get the current data:

```php
<?php

namespace Aifrus\Fc2s;

require_once(__DIR__ . '/../vendor/autoload.php');

// where do you want to save your exported zip?
$export_dir = __DIR__ . '/export';
@mkdir($export_dir);

// your SQL database credentials
$config = [
    'host' => '127.0.0.1',
    'user' => 'nasr',
    'pass' => 'nasr',
    'prefix' => 'NASR_',
    'export_dir' => $export_dir,
];

if (!Process::getCurrent($config)) die("Failed to process\n");
echo "Success\n";
```

## Documentation

For detailed documentation on using the library, refer to the following:

- [Aifrus FC2S Library Overview](docs/README.md)
- [Process Class](docs/Process.md)
- [FetchFAA Class](docs/FetchFAA.md)
- [HTTPS Class](docs/HTTPS.md)
- [Schema Class](docs/Schema.md)
- [Zip Class](docs/Zip.md)

## Notes

You must set `mysqli.allow_local_infile = On` in `php.ini` to allow `LOAD DATA LOCAL INFILE` operations within PHP scripts.
