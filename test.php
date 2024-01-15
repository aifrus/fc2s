<?php

namespace Aifrus\Fc2s;

require_once(__DIR__ . '/vendor/autoload.php');

$res = FetchFAA::fetch_current();
if (!$res) {
    echo "Failed to fetch FAA data.\n";
    exit(1);
}
echo "FAA data fetched to $res\n";
