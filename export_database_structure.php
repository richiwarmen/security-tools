<?php
$host = 'localhost';
$dbname = 'database';
$username = 'gebruikersnaam';
$password = 'wachtwoord';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Verbinding mislukt: " . $e->getMessage());
}

$tables = [];
$query = $pdo->query("SHOW TABLES");
while ($row = $query->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$tableSchemas = [];
foreach ($tables as $table) {
    $query = $pdo->query("SHOW CREATE TABLE `$table`");
    $row = $query->fetch(PDO::FETCH_ASSOC);
    $tableSchemas[$table] = $row['Create Table'];
}

$sqlDump = "-- Database: $dbname\n-- Exported at: " . date('Y-m-d H:i:s') . "\n\n";
foreach ($tableSchemas as $table => $createTableSql) {
    $sqlDump .= "-- Table: $table\n";
    $sqlDump .= "$createTableSql;\n\n";
}

$fileName = 'database_copy.sql';
file_put_contents($fileName, $sqlDump);

echo "Database structuur succesvol geëxporteerd naar bestand: $fileName\n";
echo "Inhoud voorbeeld:\n\n";
echo nl2br(htmlspecialchars(substr($sqlDump, 0, 1000)));
