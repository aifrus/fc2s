<?php

namespace Aifrus\Fc2s;

class Zip
{
    public static function extract_all(string $zip_path, string $extract_path): bool
    {
        $zip = new \ZipArchive();
        $res = $zip->open($zip_path);
        if ($res === true) {
            $zip->extractTo($extract_path);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }
}
