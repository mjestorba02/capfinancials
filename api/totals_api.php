<?php
include "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

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

// Accounts Receivable (only Paid status from collections)
$arRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM collections WHERE status = 'Paid'");
$arRow = mysqli_fetch_assoc($arRes);
$response["accounts_receivable"] = $arRow["total"] ?? 0;

// Budget Allocation (allocations table)
$allocRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM allocation");
$allocRow = mysqli_fetch_assoc($allocRes);
$response["budget_allocation"] = $allocRow["total"] ?? 0;

// Return JSON
echo json_encode($response);
