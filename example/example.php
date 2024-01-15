<?php

namespace Aifrus\Fc2s;

require_once(__DIR__ . '/vendor/autoload.php');

mkdir(__DIR__ . '/export');

$res = Process::execute([
    'host' => '127.0.0.1',
    'user' => 'aifr',
    'pass' => 'aifr',
    'export_dir' => __DIR__ . '/export',
]);

if (!$res) die("Failed to process\n");
echo "Success\n";
