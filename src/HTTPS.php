<?php

namespace Aifrus\Fc2s;

class HTTPS
{
    public static function get($url, $headers = [])
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
}
