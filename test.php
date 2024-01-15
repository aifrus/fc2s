<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\FetchFAA;

require_once(__DIR__ . '/vendor/autoload.php');

echo FetchFAA::get_home_page_html();
