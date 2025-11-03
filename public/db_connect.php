<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ✅ Railway MySQL database connection
$host = $_ENV['MYSQLHOST'] ?? 'mysql.railway.internal';
$user = $_ENV['MYSQLUSER'] ?? 'root';
$pass = $_ENV['MYSQLPASSWORD'] ?? 'WklwEbrwWJJrLcyQheVdnEyroaDohxil';
$name = $_ENV['MYSQLDATABASE'] ?? 'railway';
$port = intval($_ENV['MYSQLPORT'] ?? 3306);


$conn = @new mysqli($host, $user, $pass, $name, $port);

if ($conn->connect_errno) {
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "message" => "Database connection failed: " . $conn->connect_error
  ]);
  exit;
}

$conn->set_charset("utf8mb4");
?>


