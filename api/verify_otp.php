<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require 'db.php';

$input = json_decode(file_get_contents("php://input"), true);
$entered_otp = trim($input['otp'] ?? '');

// First, try database-backed OTP (preferred method)
$db_otp_found = false;
if (!empty($entered_otp)) {
    $stmt = $conn->prepare("
        SELECT id, user_id, email, otp_code, expires_at 
        FROM otp_sessions 
        WHERE otp_code = ? AND expires_at > NOW()
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->bind_param("i", $entered_otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $otp_record = $result->fetch_assoc();
        $db_otp_found = true;
        
        // Fetch user details
        $user_stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $otp_record['user_id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            
            // Mark OTP as used
            $update_stmt = $conn->prepare("UPDATE otp_sessions SET is_used = 1 WHERE id = ?");
            $update_stmt->bind_param("i", $otp_record['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Set session
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            
            // Clear OTP session data
            unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expires'], $_SESSION['pre_login_user']);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'OTP verified. Login successful.',
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'redirect' => 'pages/reports.php'
            ]);
            exit;
        }
        $user_stmt->close();
    }
    $stmt->close();
}

// Fallback to session-based OTP (for backward compatibility)
if (!$db_otp_found && isset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expires'], $_SESSION['pre_login_user'])) {
    if (time() > $_SESSION['otp_expires']) {
        session_unset();
        echo json_encode(['status' => 'error', 'message' => 'OTP expired.']);
        exit;
    }

    if ($entered_otp != $_SESSION['otp']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP.']);
        exit;
    }

    // OTP is valid
    $user = $_SESSION['pre_login_user'];
    $_SESSION['id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];

    // Clear OTP temp data
    unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expires'], $_SESSION['pre_login_user']);

    echo json_encode([
        'status' => 'success',
        'message' => 'OTP verified. Login successful.',
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'redirect' => 'pages/reports.php'
    ]);
    exit;
}

// If neither method worked
echo json_encode(['status' => 'error', 'message' => 'OTP session not found or expired. Please request a new OTP.']);
?>