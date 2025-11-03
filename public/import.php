<?php
// import.php — diagnostic+safe importer
header("Content-Type: text/plain; charset=utf-8");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- DB connect (Railway) ---
require_once __DIR__ . '/db_connect.php'; // must define $conn (mysqli)

// --- Quick environment checks ---
echo "PHP: " . PHP_VERSION . PHP_EOL;
echo "mysqli loaded: " . (extension_loaded('mysqli') ? "yes" : "NO") . PHP_EOL;

// --- Locate SQL file next to this script ---
$sqlFile = __DIR__ . '/riderekta_db.sql';   // <-- your real filename
echo "SQL file path: $sqlFile" . PHP_EOL;

if (!file_exists($sqlFile)) {
  http_response_code(500);
  exit("❌ SQL file not found.\nContents of current dir:\n" . implode("\n", scandir(__DIR__)));
}

echo "SQL file size: " . filesize($sqlFile) . " bytes" . PHP_EOL;

// --- Read & normalize SQL ---
// - Remove BOM, comments, empty lines
// - Strip "DELIMITER" directives (mysqli can't parse them)
// - Split on semicolons outside strings (simple heuristic)
$raw = file_get_contents($sqlFile);
$raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // BOM
$lines = preg_split("/\R/", $raw);

// strip single-line comments and DELIMITER lines
$clean = [];
foreach ($lines as $line) {
  $t = trim($line);
  if ($t === '' || str_starts_with($t, '--') || str_starts_with($t, '#') || str_starts_with(strtoupper($t), 'DELIMITER')) {
    continue;
  }
  $clean[] = $line;
}
$sql = implode("\n", $clean);

// split into statements (basic, handles most dumps without procedures)
$stmts = [];
$buf = '';
$inString = false;
$stringChar = '';
$len = strlen($sql);
for ($i=0; $i<$len; $i++) {
  $ch = $sql[$i];
  $buf .= $ch;

  if (($ch === "'" || $ch === '"')) {
    if (!$inString) { $inString = true; $stringChar = $ch; }
    elseif ($stringChar === $ch && ($i === 0 || $sql[$i-1] !== '\\')) { $inString = false; }
  }
  if (!$inString && $ch === ';') {
    $stmts[] = trim($buf);
    $buf = '';
  }
}
$buf = trim($buf);
if ($buf !== '') { $stmts[] = $buf; }

echo "Statements parsed: " . count($stmts) . PHP_EOL;

// --- Execute statements one by one for clearer errors ---
$conn->set_charset("utf8mb4");
$conn->begin_transaction();

try {
  foreach ($stmts as $idx => $stmt) {
    if ($stmt === ';' || $stmt === '') continue;
    if (!$conn->query($stmt)) {
      throw new Exception("MySQL error at statement #".($idx+1).": ".$conn->error."\n-----\n".$stmt."\n-----");
    }
  }
  $conn->commit();
  echo "✅ Database imported successfully!" . PHP_EOL;
} catch (Throwable $e) {
  $conn->rollback();
  http_response_code(500);
  echo "❌ Import failed.\n" . $e->getMessage() . PHP_EOL;
}

$conn->close();
