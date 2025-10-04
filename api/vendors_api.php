<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
include "db.php";

$sql = "SELECT DISTINCT vendor FROM payments ORDER BY vendor ASC";
$result = $conn->query($sql);

$vendors = [];
while ($row = $result->fetch_assoc()) {
  $vendors[] = $row['vendor'];
}

echo json_encode($vendors);
?>
