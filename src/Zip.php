<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\Exceptions\{
    DirectoryCreationException,
    FileNotFoundException,
    ZipException
};

/**
 * Class Zip
 * Provides methods for handling zip archives, including extraction and creation.
 */
class Zip
{
    /**
     * Extracts all files from a zip archive to a specified path.
     *
     * @param string $zip_path Path to the zip file.
     * @param string $extract_path Path to extract the files to.
     * @return bool True if the files were extracted successfully.
     * @throws FileNotFoundException If the zip file does not exist.
     * @throws DirectoryCreationException If the directory cannot be created.
     * @throws ZipException If the zip file cannot be opened or extracted.
     */
    public static function extract(string $zip_path, string $extract_path): bool
    {
        if (!file_exists($zip_path)) {
            throw new FileNotFoundException("Zip file does not exist: $zip_path");
        }

        if (!is_dir($extract_path) && !mkdir($extract_path, 0755, true) && !is_dir($extract_path)) {
            throw new DirectoryCreationException("Failed to create directory: $extract_path");
        }

        $zip = new \ZipArchive();
        if ($zip->open($zip_path) !== true) {
            throw new ZipException("Failed to open zip file: $zip_path");
        }

        if (!$zip->extractTo($extract_path)) {
            $zip->close();
            throw new ZipException("Failed to extract zip file: $zip_path to $extract_path");
        }

        $zip->close();
        return true;
    }

    /**
     * Creates a zip archive from a list of files.
     *
     * @param string $zip_path Path to the zip file to create.
     * @param array $files An array of file paths to include in the zip archive.
     * @return bool True if the zip file was created successfully.
     * @throws FileNotFoundException If any of the files do not exist.
     * @throws ZipException If the zip file cannot be created.
     */
    public static function create(string $zip_path, array $files): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($zip_path, \ZipArchive::CREATE) !== true) {
            throw new ZipException("Failed to create zip file: $zip_path");
        }

        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new FileNotFoundException("File does not exist: $file");
            }

            $zip->addFile($file, basename($file));
        }

        $zip->close();
        return true;
    }
}
