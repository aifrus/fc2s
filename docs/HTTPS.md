# HTTPS Class

The `HTTPS` class provides methods for sending HTTP requests and downloading files, facilitating interaction with web resources.

## Methods

### get

Sends a GET request to a specified URL and returns the response.

```php
use Aifrus\Fc2s\HTTPS;
$response = HTTPS::get($url, $headers);
```

- **Parameters**:
  - `$url`: The URL to send the GET request to.
  - `$headers`: Optional headers to include in the request.

### download

Downloads a file from a URL and saves it to a specified path.

```php
HTTPS::download($url, '/path/to/save', $headers);
```

- **Parameters**:
  - `$url`: The URL to download the file from.
  - `$save_path`: The path to save the downloaded file to.
  - `$headers`: Optional headers to include in the request.

## Usage

The `HTTPS` class is essential for retrieving data from the web, particularly when working with FAA datasets that require downloading files from specific URLs.