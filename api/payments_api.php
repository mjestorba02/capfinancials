<?php
include 'db.php'; // your db connection

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
$method = $_SERVER['REQUEST_METHOD'];

// Debug incoming request
file_put_contents(__DIR__ . "/debug_put.txt", 
    date("Y-m-d H:i:s") . " METHOD=" . $_SERVER['REQUEST_METHOD'] . "\n", 
    FILE_APPEND);

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

function getPaymentById($conn, $id) {
    $stmt = $conn->prepare("SELECT id, payment_id, vendor, payment_date, amount, status FROM payments WHERE id = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) return null;
    $res = $stmt->get_result();
    return $res ? $res->fetch_assoc() : null;
}

switch ($method) {
    case "OPTIONS":
        http_response_code(200);
        echo json_encode(["success" => true]);
        break;
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
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            "success" => false,
            "stage" => "json_decode",
            "error" => json_last_error_msg(),
            "raw" => $raw
        ]);
        break;
    }

    $id = isset($data['id']) ? intval($data['id']) : 0;
    if (!$id) {
        echo json_encode(["success" => false, "stage" => "id_check", "error" => "Missing ID"]);
        break;
    }

    // Start building query
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
        echo json_encode(["success" => false, "stage" => "fields_check", "error" => "No fields to update"]);
        break;
    }

    $sql = "UPDATE payments SET " . implode(", ", $fields) . " WHERE id=?";
    $params[] = $id;
    $types .= "i";

    // Debug: show query structure
    error_log("DEBUG PUT SQL: " . $sql);
    error_log("DEBUG PARAMS: " . print_r($params, true));

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "stage" => "prepare",
            "sql" => $sql,
            "error" => $conn->error
        ]);
        break;
    }

    // Bind params
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        ${"param".$i} = $params[$i];
        $bind_names[] = &${"param".$i};
    }

    if (!call_user_func_array([$stmt, 'bind_param'], $bind_names)) {
        echo json_encode(["success" => false, "stage" => "bind_param", "error" => $stmt->error]);
        break;
    }

    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "stage" => "execute", "error" => $stmt->error]);
        break;
    }

    // If we got here, update succeeded ✅
    error_log("DEBUG: Payment update OK, ID = $id");

    $warnings = [];

    // Best-effort notification insert (do not fail overall)
    try {
        $msg = "Payment #$id updated";
        $link = "payments.php?id=" . $id;
        $notif_stmt = $conn->prepare("INSERT INTO notifications (message, link) VALUES (?, ?)");
        if ($notif_stmt) {
            $notif_stmt->bind_param("ss", $msg, $link);
            if (!$notif_stmt->execute()) {
                $warnings[] = "Notification insert failed: " . $notif_stmt->error;
            }
        } else {
            $warnings[] = "Notification prepare failed: " . $conn->error;
        }
    } catch (Throwable $t) {
        $warnings[] = "Notification exception: " . $t->getMessage();
    }

    // Prepare details for journal inserts
    $current = getPaymentById($conn, $id);
    $effectiveAmount = isset($data['amount']) ? floatval($data['amount']) : (float)($current['amount'] ?? 0);
    $effectiveVendor = isset($data['vendor']) ? $data['vendor'] : ($current['vendor'] ?? '');

    // Try journal entries if status becomes Completed (best-effort)
    if (isset($data['status']) && $data['status'] === "Completed") {
        try {
            // Insert two lines: Debit AP, Credit Cash
            $sql_journal = "INSERT INTO journal_entries (entry_date, account, description, debit, credit, source_module, reference_id) VALUES (NOW(), ?, ?, ?, ?, 'Payments', ?)";
            $journal_stmt = $conn->prepare($sql_journal);
            if ($journal_stmt) {
                // 1) Debit Accounts Payable
                $account1 = "Accounts Payable";
                $desc1 = "Payment approved for vendor " . $effectiveVendor;
                $debit1 = $effectiveAmount;
                $credit1 = 0.00;
                $ref = $id;
                if (!$journal_stmt->bind_param("ssddi", $account1, $desc1, $debit1, $credit1, $ref)) {
                    $warnings[] = "Journal bind (debit) failed: " . $journal_stmt->error;
                } else if (!$journal_stmt->execute()) {
                    $warnings[] = "Journal execute (debit) failed: " . $journal_stmt->error;
                }

                // 2) Credit Cash
                $account2 = "Cash";
                $desc2 = "Cash disbursement for payment ID #" . ($current['payment_id'] ?? $id);
                $debit2 = 0.00;
                $credit2 = $effectiveAmount;
                // Need to rebind for next execution
                if (!$journal_stmt->bind_param("ssddi", $account2, $desc2, $debit2, $credit2, $ref)) {
                    $warnings[] = "Journal bind (credit) failed: " . $journal_stmt->error;
                } else if (!$journal_stmt->execute()) {
                    $warnings[] = "Journal execute (credit) failed: " . $journal_stmt->error;
                }
            } else {
                $warnings[] = "Journal prepare failed: " . $conn->error;
            }
        } catch (Throwable $t) {
            $warnings[] = "Journal exception: " . $t->getMessage();
        }
    }

    echo json_encode([
        "success" => true,
        "stage" => "done",
        "message" => "Payment updated successfully",
        "id" => $id,
        "amount" => $effectiveAmount,
        "warnings" => $warnings
    ]);
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
