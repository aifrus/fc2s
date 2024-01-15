<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\Exceptions\ProcessException;

class Process
{
    public static function execute(array $sql_config, string $export_dir): bool
    {
        $data_dir = FetchFAA::fetch_current();
        if (!$data_dir) throw new ProcessException("Failed to fetch FAA data.");

        $statements = Schema::generate($data_dir);
        if (!$statements) throw new ProcessException("Failed to generate schema.");

        return true;
    }
}
