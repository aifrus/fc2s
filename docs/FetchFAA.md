# FetchFAA Class

The `FetchFAA` class provides methods to interact with FAA data sources, allowing you to retrieve URLs and available dates for datasets.

## Methods

### get_home_page_html

Fetches the HTML content of the FAA home page.

```php
use Aifrus\Fc2s\FetchFAA;
$html = FetchFAA::get_home_page_html();
```

### get_current_date

Retrieves the current dataset date.

```php
$currentDate = FetchFAA::get_current_date();
```

### get_data_file_url

Generates the URL for a dataset file based on a given date.

```php
$url = FetchFAA::get_data_file_url($date);
```

### get_available_dates

Returns an array of available dataset dates.

```php
$dates = FetchFAA::get_available_dates();
```

## Usage

The `FetchFAA` class is useful for obtaining the necessary URLs and dates to download FAA datasets, which can then be processed using other classes in the library.