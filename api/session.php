<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['logged_in' => false]);
    exit;
}

$userId = intval($_SESSION['id']);
$stmt = $conn->prepare("SELECT id, name, email, account_type FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $user = $res->fetch_assoc();
    echo json_encode([ 'logged_in' => true, 'user' => $user ]);
} else {
    echo json_encode(['logged_in' => false]);
}
$stmt->close();
?>