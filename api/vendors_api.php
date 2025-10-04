<?php
// api/vendors_api.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---------- HEADERS ----------
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ---------- PRE-FLIGHT (OPTIONS) ----------
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ---------- DATABASE ----------
include "db.php"; // make sure this path is correct

// ---------- QUERY ----------
$sql = "SELECT vendor, amount FROM payments";
$result = $conn->query($sql);

// ---------- ERROR HANDLING ----------
if (!$result) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database query failed",
        "details" => $conn->error
    ]);
    exit;
}

// ---------- BUILD RESPONSE ----------
$vendors = [];
while ($row = $result->fetch_assoc()) {
    $vendors[] = [
        "vendor" => $row['vendor'],
        "amount" => (float)$row['amount']
    ];
}

// ---------- OUTPUT ----------
echo json_encode([
    "success" => true,
    "data" => $vendors
], JSON_UNESCAPED_UNICODE);

$conn->close();