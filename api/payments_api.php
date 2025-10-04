<?php
include 'db.php'; // your db connection

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
$method = $_SERVER['REQUEST_METHOD'];

// Generate Payment ID
function generatePaymentId($conn) {
    $result = $conn->query("SELECT payment_id FROM payments ORDER BY id DESC LIMIT 1");
    if ($result->num_rows > 0) {
        $lastId = $result->fetch_assoc()['payment_id'];
        $num = intval(substr($lastId, 4)) + 1;
        return "PAY-" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        return "PAY-001";
    }
}

switch ($method) {
    case "GET":
        $res = $conn->query("SELECT * FROM payments ORDER BY id DESC");
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode($rows);
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $payment_id = generatePaymentId($conn);
        $vendor = $data['vendor'];
        $payment_date = $data['payment_date'];
        $amount = $data['amount'];
        $status = $data['status'];

        $stmt = $conn->prepare("INSERT INTO payments (payment_id, vendor, payment_date, amount, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $payment_id, $vendor, $payment_date, $amount, $status);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "payment_id" => $payment_id]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        break;

    case "PUT":
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Missing ID"]);
        break;
    }

    // Build dynamic SET clause
        $fields = [];
        $params = [];
        $types = "";

        if (!empty($data['vendor'])) {
            $fields[] = "vendor=?";
            $params[] = $data['vendor'];
            $types .= "s";
        }
        if (!empty($data['payment_date'])) {
            $fields[] = "payment_date=?";
            $params[] = $data['payment_date'];
            $types .= "s";
        }
        if (!empty($data['amount'])) {
            $fields[] = "amount=?";
            $params[] = $data['amount'];
            $types .= "d";
        }
        if (!empty($data['status'])) {
            $fields[] = "status=?";
            $params[] = $data['status'];
            $types .= "s";
        }

        if (empty($fields)) {
            echo json_encode(["success" => false, "message" => "No fields to update"]);
            break;
        }

        $sql = "UPDATE payments SET " . implode(", ", $fields) . " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Notification
            $msg = "Payment #$id updated";
            $link = "payments.php?id=" . $id;
            $notif_stmt = $conn->prepare("INSERT INTO notifications (message, link) VALUES (?, ?)");
            $notif_stmt->bind_param("ss", $msg, $link);
            $notif_stmt->execute();

            // Insert into journal if approved
            if (!empty($data['status']) && $data['status'] === "Completed") {
                $journal_stmt = $conn->prepare("INSERT INTO journal_entries (entry_date, account, description, debit, source_module, reference_id) VALUES (NOW(), ?, ?, ?, 'Payments', ?)");
                $account = "Accounts Payable";
                $desc = "Payment approved for vendor " . ($data['vendor'] ?? '');
                $amt = $data['amount'] ?? 0;
                $journal_stmt->bind_param("ssdi", $account, $desc, $amt, $id);
                $journal_stmt->execute();
            }

            echo json_encode(["success" => true, "message" => "Payment updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update payment"]);
        }
        break;


    case "DELETE":
        parse_str(file_get_contents("php://input"), $data);
        $id = $data['id'];
        $stmt = $conn->prepare("DELETE FROM payments WHERE id=?");
        $stmt->bind_param("i", $id);

        echo json_encode(["success" => $stmt->execute()]);
        break;

    default:
        echo json_encode(["error" => "Invalid request"]);
}
?>
