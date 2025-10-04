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
        // Read JSON input
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        // JSON decode check
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid JSON",
                "json_error" => json_last_error_msg(),
                "raw" => $raw
            ]);
            break;
        }

        $id = isset($data['id']) ? intval($data['id']) : 0;
        if (!$id) {
            echo json_encode(["success" => false, "message" => "Missing ID"]);
            break;
        }

        // Build dynamic SET clause using isset (so passing empty string intentionally will update)
        $fields = [];
        $params = [];
        $types = "";

        if (isset($data['vendor'])) {
            $fields[] = "vendor=?";
            $params[] = $data['vendor'];
            $types .= "s";
        }
        if (isset($data['payment_date'])) {
            $fields[] = "payment_date=?";
            $params[] = $data['payment_date'];
            $types .= "s";
        }
        if (isset($data['amount'])) {
            // cast to float to be safe
            $fields[] = "amount=?";
            $params[] = floatval($data['amount']);
            $types .= "d";
        }
        if (isset($data['status'])) {
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
        if (!$stmt) {
            echo json_encode([
                "success" => false,
                "message" => "Prepare failed",
                "error" => $conn->error,
                "sql" => $sql
            ]);
            break;
        }

        // bind_param requires arguments passed by reference. Build array of references:
        $bind_names = [];
        $bind_names[] = $types;
        // create variables by reference
        for ($i = 0; $i < count($params); $i++) {
            // create variable names to hold values so we can pass references to bind_param
            ${"param".$i} = $params[$i];
            $bind_names[] = &${"param".$i};
        }

        // call bind_param with call_user_func_array
        $bindResult = call_user_func_array([$stmt, 'bind_param'], $bind_names);
        if ($bindResult === false) {
            echo json_encode([
                "success" => false,
                "message" => "bind_param failed",
                "error" => $stmt->error
            ]);
            break;
        }

        if ($stmt->execute()) {
            // Notification
            $msg = "Payment #$id updated";
            $link = "payments.php?id=" . $id;
            $notif_stmt = $conn->prepare("INSERT INTO notifications (message, link) VALUES (?, ?)");
            if ($notif_stmt) {
                $notif_stmt->bind_param("ss", $msg, $link);
                $notif_stmt->execute();
            }

            // Insert into journal if approved
            if (isset($data['status']) && $data['status'] === "Completed") {
                $journal_stmt = $conn->prepare("INSERT INTO journal_entries (entry_date, account, description, debit, source_module, reference_id) VALUES (NOW(), ?, ?, ?, 'Payments', ?)");
                if ($journal_stmt) {
                    $account = "Accounts Payable";
                    $desc = "Payment approved for vendor " . ($data['vendor'] ?? '');
                    $amt = isset($data['amount']) ? floatval($data['amount']) : 0.00;
                    $journal_stmt->bind_param("ssdi", $account, $desc, $amt, $id);
                    $journal_stmt->execute();
                }
            }

            echo json_encode([
                "success" => true,
                "message" => "Payment updated successfully",
                // return the amount so frontend can display it
                "amount" => isset($data['amount']) ? floatval($data['amount']) : null,
                "id" => $id
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to update payment",
                "error" => $stmt->error
            ]);
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
