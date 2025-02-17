<?php

namespace Aifrus\Fc2s;

require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Script to get the latest FAA data and export it.
 * 
 * This script initializes the configuration and attempts to fetch the latest
 * FAA data, exporting it to the specified directory.
 */

// Directory to save exported data
$exportDir = __DIR__ . '/export';
if (!is_dir($exportDir) && !mkdir($exportDir, 0755, true)) {
    die("Failed to create export directory\n");
}

// Database configuration
$config = [
    'host' => '127.0.0.1',
    'user' => 'nasr',
    'pass' => 'nasr',
    'prefix' => 'NASR_',
    'export_dir' => $exportDir,
];

// Fetch the latest data
if (!Process::get_latest($config)) {
    die("Failed to process\n");
}

echo "Success\n";
