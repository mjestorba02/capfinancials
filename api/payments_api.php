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
    header("Content-Type: application/json");

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

    // Try notification insert
    $msg = "Payment #$id updated";
    $link = "payments.php?id=" . $id;
    $notif_stmt = $conn->prepare("INSERT INTO notifications (message, link) VALUES (?, ?)");
    if (!$notif_stmt) {
        echo json_encode(["success" => false, "stage" => "notif_prepare", "error" => $conn->error]);
        break;
    }
    if (!$notif_stmt->bind_param("ss", $msg, $link)) {
        echo json_encode(["success" => false, "stage" => "notif_bind", "error" => $notif_stmt->error]);
        break;
    }
    if (!$notif_stmt->execute()) {
        echo json_encode(["success" => false, "stage" => "notif_execute", "error" => $notif_stmt->error]);
        break;
    }

    // Try journal entry insert if completed
    if (isset($data['status']) && $data['status'] === "Completed") {
        $sql_journal = "INSERT INTO journal_entries (entry_date, account, description, debit, source_module, reference_id) 
                        VALUES (NOW(), ?, ?, ?, 'Payments', ?)";
        $journal_stmt = $conn->prepare($sql_journal);
        if (!$journal_stmt) {
            echo json_encode(["success" => false, "stage" => "journal_prepare", "error" => $conn->error]);
            break;
        }

        $account = "Accounts Payable";
        $desc = "Payment approved for vendor " . ($data['vendor'] ?? '');
        $amt = isset($data['amount']) ? floatval($data['amount']) : 0.00;

        if (!$journal_stmt->bind_param("ssdi", $account, $desc, $amt, $id)) {
            echo json_encode(["success" => false, "stage" => "journal_bind", "error" => $journal_stmt->error]);
            error_log("JOURNAL BIND ERROR: " . $journal_stmt->error);
            break;
        }

        if (!$journal_stmt->execute()) {
            echo json_encode([
                "success" => false,
                "stage" => "journal_execute",
                "error" => $journal_stmt->error,
                "sql" => $sql_journal,
                "values" => compact('account', 'desc', 'amt', 'id')
            ]);
            error_log("JOURNAL EXECUTE ERROR: " . $journal_stmt->error);
            break;
        }

        error_log("DEBUG: Journal entry inserted successfully for payment ID $id");
    }

    echo json_encode([
        "success" => true,
        "stage" => "done",
        "message" => "Payment updated successfully",
        "id" => $id
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
