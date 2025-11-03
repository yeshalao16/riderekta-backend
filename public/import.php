<?php
include 'db_connect.php';
$sql = file_get_contents('railway.sql');
if ($conn->multi_query($sql)) {
    echo "✅ Database imported successfully!";
} else {
    echo "❌ Error: " . $conn->error;
}
?>
