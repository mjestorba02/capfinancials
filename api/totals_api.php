<?php
include "db.php";
header("Content-Type: application/json");

// Prepare response array
$response = [];

// Collections
$colRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM collections");
$colRow = mysqli_fetch_assoc($colRes);
$response["collections"] = $colRow["total"] ?? 0;

// Disbursements
$disRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM disbursements");
$disRow = mysqli_fetch_assoc($disRes);
$response["disbursements"] = $disRow["total"] ?? 0;

// Accounts Payable (payments table)
$payRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments");
$payRow = mysqli_fetch_assoc($payRes);
$response["accounts_payable"] = $payRow["total"] ?? 0;

// Accounts Receivable (same as collections)
$response["accounts_receivable"] = $response["collections"];

// Return JSON
echo json_encode($response);
