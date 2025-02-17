# FetchFAA Class

The `FetchFAA` class provides methods to interact with FAA data sources, allowing you to retrieve URLs and available dates for datasets.

## Methods

### getHomePageHtml

Fetches the HTML content of the FAA home page.

```php
use Aifrus\Fc2s\FetchFAA;
$html = FetchFAA::getHomePageHtml();
```

### getCurrentDate

Retrieves the current dataset date.

```php
$currentDate = FetchFAA::getCurrentDate();
```

### getDataFileUrl

Generates the URL for a dataset file based on a given date.

```php
$url = FetchFAA::getDataFileUrl($date);
```

### getAvailableDates

Returns an array of available dataset dates.

```php
$dates = FetchFAA::getAvailableDates();
```

## Usage

The `FetchFAA` class is useful for obtaining the necessary URLs and dates to download FAA datasets, which can then be processed using other classes in the library.