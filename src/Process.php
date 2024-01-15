<?php

namespace Aifrus\Fc2s;

use Aifrus\Fc2s\Exceptions\ProcessException;
use mysqli;

class Process
{
    private ?mysqli $sql = null;
    private ?string $export_dir = null;

    public function __construct(array $config)
    {
        $this->export_dir = $config['export_dir'] ?? null;
        $host = $config['host'] ?? null;
        $user = $config['user'] ?? null;
        $pass = $config['pass'] ?? null;
        if (!$host || !$user || !$pass) throw new ProcessException("Missing database credentials.");
        $this->sql = new mysqli($host, $user, $pass);
        if ($this->sql->connect_error) throw new ProcessException("Failed to connect to database: {$this->sql->connect_error}");
    }

    public static function execute(array $config): bool
    {
        return (new self($config))->process();
    }

    public function process(): bool
    {
        $data_dir = FetchFAA::fetch_current();
        if (!$data_dir) throw new ProcessException("Failed to fetch FAA data.");

        $statements = Schema::generate($data_dir);
        if (!$statements) throw new ProcessException("Failed to generate schema.");

        print_r($statements);
        return true;
    }
}
