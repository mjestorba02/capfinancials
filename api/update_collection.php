<?php
include "db.php";
header("Content-Type: application/json");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Check _method=PUT
if (empty($_POST['_method']) || strtoupper($_POST['_method']) !== 'PUT') {
    echo json_encode(["status" => "error", "message" => "_method=PUT is required"]);
    exit;
}

// Validate invoice_no
$invoice_no = $_POST['invoice_no'] ?? '';
if (!$invoice_no) {
    echo json_encode(["status" => "error", "message" => "Invoice number required"]);
    exit;
}

$invoice_no = mysqli_real_escape_string($conn, $invoice_no);
$customer   = mysqli_real_escape_string($conn, $_POST['customer'] ?? '');
$department = mysqli_real_escape_string($conn, $_POST['department'] ?? '');
$status     = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
$date       = mysqli_real_escape_string($conn, $_POST['date'] ?? '');
$amount     = isset($_POST['amount']) && is_numeric($_POST['amount']) ? floatval($_POST['amount']) : null;

if ($amount === null) {
    echo json_encode(["status" => "error", "message" => "Invalid amount"]);
    exit;
}

// Update query
$sql = "UPDATE collections 
        SET customer='$customer', department='$department', amount=$amount, status='$status', date='$date'
        WHERE invoice_no='$invoice_no'";

if (!mysqli_query($conn, $sql)) {
    echo json_encode(["status" => "error", "message" => "MySQL Error: " . mysqli_error($conn)]);
    exit;
}

if (mysqli_affected_rows($conn) > 0) {
    // Insert notification
    $msg  = "Collection Invoice #$invoice_no updated. Status: $status";
    $link = "collections.php?invoice_no=" . urlencode($invoice_no);
    mysqli_query($conn, "INSERT INTO notifications (module, record_id, message, link) 
                         VALUES ('collections', '$invoice_no', '$msg', '$link')");

    echo json_encode(["status" => "success", "message" => "Collection updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "No changes made or invoice not found"]);
}
