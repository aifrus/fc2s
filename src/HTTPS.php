<?php

namespace Aifrus\Fc2s;

class HTTPS
{
    public static function get($url, $headers = [])
    {
        return file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
            ],
        ]));
    }
}
