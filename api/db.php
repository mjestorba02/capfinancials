<?php
// db.php - Database Connection (MySQLi)

// $host = "localhost";       // Database host
// $user = "fina_admin";            // Database username
// $password = "C@c2lRE9BVCOUzv1";            // Database password
// $dbname = "fina_financial_hostipal"; // Database name

$host = "localhost";       // Database host
$user = "root";            // Database username
$password = "";            // Database password
$dbname = "capfinancial"; // Database name

// Create connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . mysqli_connect_error()
    ]);
    exit;
}

// Optional: Set charset
mysqli_set_charset($conn, "utf8");
?>
