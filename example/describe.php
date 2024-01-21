<?php
$lines[] = 'TABLE,FIELD,TYPE,NULL,KEY,DEFAULT,EXTRA';
$mysqli = new mysqli("127.0.0.1", "aifr", "aifr", "NASR_INDEX");
if ($mysqli->connect_errno) die("Failed to connect to MySQL: " . $mysqli->connect_error);
extract($mysqli->query("SELECT `name` FROM `INDEX` ORDER BY `id` DESC LIMIT 1")->fetch_assoc());
$mysqli->select_db($name);
$tables = $mysqli->query("SHOW TABLES")->fetch_all(MYSQLI_NUM);
foreach ($tables as $table) {
    $description = $mysqli->query("DESCRIBE `{$table[0]}`");
    while ($row = $description->fetch_assoc()) $lines[] = $table[0] . ',' . implode(",", $row);
}
$mysqli->close();
file_put_contents("describe.txt", implode("\n", $lines));
