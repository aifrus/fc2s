<?php

namespace Aifrus\Fc2s;

require_once(__DIR__ . '/../vendor/autoload.php');

$export_dir = __DIR__ . '/export';
@mkdir($export_dir);

$config = [
    'host' => '127.0.0.1',
    'user' => 'aifr',
    'pass' => 'aifr',
    'prefix' => 'NASR_',
    'export_dir' => $export_dir,
];

// Get All
//if (!Process::get_all($config)) die("Failed to process\n");
//echo "Success\n";

// Get Latest
if (!Process::get_latest($config)) die("Failed to process\n");
echo "Success\n";
