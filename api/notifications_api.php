<?php
include "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Fetch notifications
if ($action === "get_notifications") {
    $res = mysqli_query($conn, "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
    $notifs = [];
    $unread = 0;

    while ($row = mysqli_fetch_assoc($res)) {
        $notifs[] = $row;
        if ($row["is_read"] == 0) $unread++;
    }

    echo json_encode([
        "success" => true,
        "notifications" => $notifs,
        "unread_count" => $unread
    ]);
    exit;
}

// Mark single notification read
if ($action === "mark_read" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE id=$id");
    echo json_encode(["success" => true]);
    exit;
}

// Mark all notifications read
if ($action === "mark_read_all") {
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE is_read=0");
    echo json_encode(["success" => true]);
    exit;
}

// Delete single notification
if ($action === "delete" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    mysqli_query($conn, "DELETE FROM notifications WHERE id=$id");
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid action"]);
