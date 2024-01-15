<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\FetchFAA;

require_once(__DIR__ . '/vendor/autoload.php');

print_r(FetchFAA::get_available_dates());
