<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
include "db.php";

$result = $conn->query("SELECT vendor, amount FROM account_payable");
$vendors = [];

while ($row = $result->fetch_assoc()) {
  $vendors[] = [
    "vendor" => $row['vendor'],
    "amount" => $row['amount']
  ];
}

echo json_encode($vendors);
?>
