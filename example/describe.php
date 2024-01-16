<?php
$mysqli = new mysqli("127.0.0.1", "aifr", "aifr", "NASR_2023-12-28");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

// Get all table names
$result = $mysqli->query("SHOW TABLES");
$tables = $result->fetch_all(MYSQLI_NUM);
echo "----\n";
foreach ($tables as $table) {
    $tableName = $table[0];

    echo "Table {$tableName}:\n";
    $description = $mysqli->query("DESCRIBE `{$tableName}`");
    while ($row = $description->fetch_assoc()) echo implode(",", $row) . "\n";
    // echo "\nRandom Examples from {$tableName}:\n";
    // $randomRows = $mysqli->query("SELECT * FROM `{$tableName}` ORDER BY RAND() LIMIT 5");
    // while ($row = $randomRows->fetch_assoc()) {
    //     echo implode(",", $row) . "\n";
    // }
    echo "----\n";
}

$mysqli->close();
