<?php
// users.php - RESTful API for Users (no role)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Helper to sanitize input
function sanitize($conn, $val) {
    return mysqli_real_escape_string($conn, trim($val));
}

// Parse JSON input
$input = json_decode(file_get_contents("php://input"), true) ?? [];

// --- GET: Fetch users ---
if ($method === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($id) {
        $sql = "SELECT id, name, email, created_at FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo json_encode($res ?: ['status'=>'error','message'=>'User not found']);
    } else {
        $res = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY id DESC");
        $users = [];
        while ($row = $res->fetch_assoc()) $users[] = $row;
        echo json_encode($users);
    }
    exit;
}

// --- POST: Create user ---
if ($method === 'POST') {
    $name = sanitize($conn, $input['name'] ?? '');
    $email = sanitize($conn, $input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Name, email, and password are required']);
        exit;
    }

    // Check email existence
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User created']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    $stmt->close();
    exit;
}

// --- PUT: Update user ---
if ($method === 'PUT') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) { echo json_encode(['status' => 'error', 'message' => 'User ID required in URL']); exit; }

    $name = sanitize($conn, $input['name'] ?? '');
    $email = sanitize($conn, $input['email'] ?? '');
    $password = $input['password'] ?? null;

    $sql = "UPDATE users SET name=?, email=?";
    $types = "ss";
    $params = [$name, $email];

    if ($password) {
        $sql .= ", password=?";
        $types .= "s";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id=?";
    $types .= "i";
    $params[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// --- DELETE: Remove user ---
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) { echo json_encode(['status' => 'error', 'message' => 'User ID required in URL']); exit; }

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// Default fallback
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
