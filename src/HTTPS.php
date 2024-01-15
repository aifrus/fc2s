<?php

namespace Aifrus\Fc2s;

class HTTPS
{
    public static function get(string $url, array $headers = []): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0); // Use HTTP/2.0
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Enable all supported encodings
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    public static function download(string $url, string $save_path, array $headers = []): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Don't return the result
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0); // Use HTTP/2.0
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Enable all supported encodings

        $file = fopen($save_path, 'w');
        curl_setopt($ch, CURLOPT_FILE, $file); // Write output directly to the file

        curl_exec($ch);

        $error = curl_error($ch);
        curl_close($ch);
        fclose($file);

        // Check if the file was created and its size is greater than zero
        if (!file_exists($save_path) || !filesize($save_path)) return false;
        return true;
    }
}
