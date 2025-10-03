<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require 'db.php';
require '../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = json_decode(file_get_contents("php://input"), true);

// Validate email
if (!isset($input['email']) || empty(trim($input['email']))) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

// Validate password
if (!isset($input['password']) || strlen(trim($input['password'])) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
    exit;
}

$email = mysqli_real_escape_string($conn, trim($input['email']));
$password = trim($input['password']);

// Fetch user (removed role)
$stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    exit;
}

// Generate OTP
$otp = random_int(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_email'] = $user['email'];
$_SESSION['otp_expires'] = time() + 300; // valid for 5 minutes
$_SESSION['pre_login_user'] = $user; // store user temporarily

// Send OTP via PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP config
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'sanchezlando333@gmail.com'; // Replace with your Gmail
    $mail->Password = 'hrol elld wdxc ayzf';       // Replace with your App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email content
    $mail->setFrom('your_email@gmail.com', 'Financial');
    $mail->addAddress($user['email'], $user['name']);
    $mail->Subject = 'Your OTP Code';
    $mail->Body = "Hi {$user['name']},\n\nYour OTP is: {$otp}\n\nIt expires in 5 minutes.";

    $mail->send();

    echo json_encode([
        'status' => 'otp_required',
        'message' => 'OTP sent to your email',
        'email' => $user['email']
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP.']);
}
?>
