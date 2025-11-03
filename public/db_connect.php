<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// --- Read DB credentials from environment variables ---
$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$name = $_ENV['DB_NAME'] ?? 'riderekta_db';
$port = intval($_ENV['DB_PORT'] ?? 3306);

$conn = @new mysqli($host, $user, $pass, $name, $port);

if ($conn->connect_errno) {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Database connection failed."]);
  exit;
}

$conn->set_charset("utf8mb4");
?>
