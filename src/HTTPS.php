<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\Exceptions\{
    CurlException,
    FileWriteException,
    DirectoryCreationException
};

class HTTPS
{
    /**
     * Sends a GET request to a URL and returns the response.
     *
     * @param string $url The URL to send the GET request to.
     * @param array $headers Optional headers to include in the request.
     * @return string The response from the URL.
     * @throws CurlException If the cURL operation fails.
     */
    public static function get(string $url, array $headers = []): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new CurlException("cURL error occurred: $error");
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Downloads a file from a URL and saves it to a specified path.
     *
     * @param string $url The URL to download the file from.
     * @param string $save_path The path to save the downloaded file to.
     * @param array $headers Optional headers to include in the request.
     * @return bool True if the file was downloaded and saved successfully.
     * @throws CurlException If the cURL operation fails.
     * @throws FileWriteException If the file cannot be written to the specified path.
     * @throws DirectoryCreationException If the directory cannot be created.
     * @throws FileWriteException If the file cannot be written to the specified path.
     */
    public static function download(string $url, string $save_path, array $headers = []): bool
    {
        $dir = dirname($save_path);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new DirectoryCreationException("Failed to create directory: $dir");
        }

        if (!is_writable($dir)) {
            throw new FileWriteException("Directory is not writable: $dir");
        }

        if (file_exists($save_path)) {
            unlink($save_path);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $file = fopen($save_path, 'w');
        if ($file === false) {
            throw new FileWriteException("Failed to open file for writing: $save_path");
        }

        curl_setopt($ch, CURLOPT_FILE, $file);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            fclose($file);
            throw new CurlException("cURL error occurred during download: $error");
        }

        curl_close($ch);
        fclose($file);

        if (!file_exists($save_path) || filesize($save_path) === 0) {
            throw new FileWriteException("Downloaded file is empty or could not be saved: $save_path");
        }

        return true;
    }
}
