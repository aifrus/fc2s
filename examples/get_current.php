<?php

namespace Aifrus\Fc2s;

require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Script to get the current FAA data and export it.
 * 
 * This script initializes the configuration and attempts to fetch the current
 * FAA data, exporting it to the specified directory.
 */

// Directory to save exported data
$exportDir = __DIR__ . '/export';
if (!is_dir($exportDir) && !mkdir($exportDir, 0755, true)) {
    die("Failed to create export directory\n");
}

// Database configuration
$config = [
    'host' => 'mysql',
    'user' => 'nasr',
    'pass' => 'nasr',
    'prefix' => 'NASR_',
    'export_dir' => $exportDir,
];

// Fetch the current data
if (!Process::getCurrent($config)) {
    die("Failed to process\n");
}

echo "Success\n";
