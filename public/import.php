<?php
include 'db_connect.php';

header("Content-Type: text/plain; charset=utf-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$sqlFile = __DIR__ . '/riderekta_db.sql';

if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: $sqlFile");
}

$sql = file_get_contents($sqlFile);

if (!$sql) {
    die("❌ Failed to read SQL file contents.");
}

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "✅ Database imported successfully!";
} else {
    echo "❌ MySQL Error: " . $conn->error;
}

$conn->close();
?>
