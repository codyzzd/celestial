<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
  fwrite(STDERR, "This script must be run from the command line.\n");
  exit(1);
}

$rootPath = dirname(__DIR__);
$defaultOutput = $rootPath . '/database/schema.sql';
$outputPath = $argv[1] ?? $defaultOutput;
$configPath = getenv('DBCON_PATH') ?: $rootPath . '/resources/dbcon.php';

if (!is_file($configPath)) {
  fwrite(STDERR, "Database config not found: {$configPath}\n");
  fwrite(STDERR, "Set DBCON_PATH or create resources/dbcon.php before exporting.\n");
  exit(1);
}

$config = require $configPath;

foreach (['host', 'username', 'password', 'database'] as $key) {
  if (!array_key_exists($key, $config)) {
    fwrite(STDERR, "Missing database config key: {$key}\n");
    exit(1);
  }
}

$host = (string) $config['host'];
$username = (string) $config['username'];
$password = (string) $config['password'];
$database = (string) $config['database'];
$port = (int) ($config['port'] ?? 3306);

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
  fwrite(STDERR, "Database connection failed: {$conn->connect_error}\n");
  exit(1);
}

$conn->set_charset('utf8mb4');

$schemaName = fetchValue($conn, 'SELECT DATABASE()');
$baseTables = fetchObjects($conn, "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
$views = fetchObjects($conn, "SHOW FULL TABLES WHERE Table_type = 'VIEW'");
$tableNameKey = "Tables_in_{$schemaName}";

$tables = array_map(static fn (array $row): string => $row[$tableNameKey], $baseTables);
$orderedTables = orderTablesByDependencies($conn, $schemaName, $tables);

$sql = [];
$sql[] = '-- Celestial database structure export';
$sql[] = '-- Generated at: ' . gmdate('Y-m-d H:i:s') . ' UTC';
$sql[] = '-- Source database: ' . quoteIdentifier($schemaName);
$sql[] = '-- Data rows are intentionally not included.';
$sql[] = '';
$sql[] = 'SET NAMES utf8mb4;';
$sql[] = 'SET FOREIGN_KEY_CHECKS=0;';
$sql[] = '';

foreach ($orderedTables as $table) {
  $createTable = fetchCreateStatement($conn, 'TABLE', $table);
  $sql[] = "DROP TABLE IF EXISTS " . quoteIdentifier($table) . ';';
  $sql[] = $createTable . ';';
  $sql[] = '';
}

foreach ($views as $row) {
  $view = $row[$tableNameKey];
  $createView = normalizeCreateStatement(fetchCreateStatement($conn, 'VIEW', $view));
  $sql[] = "DROP VIEW IF EXISTS " . quoteIdentifier($view) . ';';
  $sql[] = $createView . ';';
  $sql[] = '';
}

appendRoutines($conn, $schemaName, $sql, 'PROCEDURE');
appendRoutines($conn, $schemaName, $sql, 'FUNCTION');
appendTriggers($conn, $sql);

$sql[] = 'SET FOREIGN_KEY_CHECKS=1;';
$sql[] = '';

$outputDir = dirname($outputPath);
if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
  fwrite(STDERR, "Could not create output directory: {$outputDir}\n");
  exit(1);
}

file_put_contents($outputPath, implode("\n", $sql));
echo "Schema exported to {$outputPath}\n";

$conn->close();

function fetchObjects(mysqli $conn, string $sql): array
{
  $result = $conn->query($sql);
  if (!$result) {
    throw new RuntimeException($conn->error);
  }

  return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchValue(mysqli $conn, string $sql): string
{
  $rows = fetchObjects($conn, $sql);
  return (string) array_values($rows[0])[0];
}

function fetchCreateStatement(mysqli $conn, string $type, string $name): string
{
  $result = $conn->query('SHOW CREATE ' . $type . ' ' . quoteIdentifier($name));
  if (!$result) {
    throw new RuntimeException($conn->error);
  }

  $row = $result->fetch_assoc();
  return (string) ($row['Create ' . ucfirst(strtolower($type))] ?? array_values($row)[1]);
}

function orderTablesByDependencies(mysqli $conn, string $schemaName, array $tables): array
{
  $tableSet = array_fill_keys($tables, true);
  $deps = array_fill_keys($tables, []);

  $stmt = $conn->prepare(
    'SELECT TABLE_NAME, REFERENCED_TABLE_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = ?
       AND REFERENCED_TABLE_SCHEMA = ?
       AND REFERENCED_TABLE_NAME IS NOT NULL'
  );
  $stmt->bind_param('ss', $schemaName, $schemaName);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $table = $row['TABLE_NAME'];
    $referenced = $row['REFERENCED_TABLE_NAME'];
    if (isset($tableSet[$table], $tableSet[$referenced])) {
      $deps[$table][$referenced] = true;
    }
  }

  $ordered = [];
  $temporary = [];
  $permanent = [];

  $visit = function (string $table) use (&$visit, &$deps, &$ordered, &$temporary, &$permanent): void {
    if (isset($permanent[$table])) {
      return;
    }

    if (isset($temporary[$table])) {
      return;
    }

    $temporary[$table] = true;
    foreach (array_keys($deps[$table] ?? []) as $dependency) {
      $visit($dependency);
    }
    unset($temporary[$table]);

    $permanent[$table] = true;
    $ordered[] = $table;
  };

  foreach ($tables as $table) {
    $visit($table);
  }

  return $ordered;
}

function appendRoutines(mysqli $conn, string $schemaName, array &$sql, string $type): void
{
  $rows = fetchObjects($conn, "SHOW {$type} STATUS WHERE Db = '" . $conn->real_escape_string($schemaName) . "'");
  if (!$rows) {
    return;
  }

  $sql[] = 'DELIMITER ;;';
  foreach ($rows as $row) {
    $name = $row['Name'];
    $create = normalizeCreateStatement(fetchCreateStatement($conn, $type, $name));
    $sql[] = 'DROP ' . $type . ' IF EXISTS ' . quoteIdentifier($name) . ';;';
    $sql[] = $create . ';;';
    $sql[] = '';
  }
  $sql[] = 'DELIMITER ;';
  $sql[] = '';
}

function appendTriggers(mysqli $conn, array &$sql): void
{
  $triggers = fetchObjects($conn, 'SHOW TRIGGERS');
  if (!$triggers) {
    return;
  }

  $sql[] = 'DELIMITER ;;';
  foreach ($triggers as $trigger) {
    $name = $trigger['Trigger'];
    $create = normalizeCreateStatement(fetchCreateStatement($conn, 'TRIGGER', $name));
    $sql[] = 'DROP TRIGGER IF EXISTS ' . quoteIdentifier($name) . ';;';
    $sql[] = $create . ';;';
    $sql[] = '';
  }
  $sql[] = 'DELIMITER ;';
  $sql[] = '';
}

function normalizeCreateStatement(string $sql): string
{
  $sql = preg_replace('/\s+DEFINER=`[^`]+`@`[^`]+`/i', '', $sql) ?? $sql;
  return preg_replace('/^CREATE\s+ALGORITHM=\w+\s+SQL\s+SECURITY\s+\w+\s+VIEW/i', 'CREATE VIEW', $sql) ?? $sql;
}

function quoteIdentifier(string $identifier): string
{
  return '`' . str_replace('`', '``', $identifier) . '`';
}
