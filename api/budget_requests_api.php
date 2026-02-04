<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";
session_start();

$method = $_SERVER['REQUEST_METHOD'];

// Log every request
error_log("[budget_requests_api.php DEBUG] Request method: " . $method);

switch ($method) {
    case "GET":
        error_log("[budget_requests_api.php DEBUG] GET request triggered");

        // If ?available=true, return only departments not in allocations & not approved
        if (isset($_GET['available']) && $_GET['available'] == 'true') {
            $query = "
                SELECT * FROM budget_requests
                WHERE department NOT IN (SELECT department FROM allocation)
                AND status != 'Approved'
                ORDER BY created_at DESC
            ";
            error_log("[budget_requests_api.php DEBUG] Filtering available departments only");
        } else {
            $query = "SELECT * FROM budget_requests ORDER BY created_at DESC";
        }

        $res = $conn->query($query);

        if (!$res) {
            error_log("[budget_requests_api.php ERROR] SQL Error: " . $conn->error);
            echo json_encode(["success" => false, "error" => $conn->error]);
            exit;
        }

        $requests = [];
        while ($row = $res->fetch_assoc()) $requests[] = $row;

        error_log("[budget_requests_api.php DEBUG] Returned " . count($requests) . " rows");
        echo json_encode($requests);
        break;

    case "POST":
        error_log("[budget_requests_api.php DEBUG] POST request triggered");
        $data = json_decode(file_get_contents("php://input"), true);
        error_log("[budget_requests_api.php DEBUG] Raw input: " . json_encode($data));

        $department = $conn->real_escape_string($data['department']);
        $purpose = $conn->real_escape_string($data['purpose']);
        $amount = $conn->real_escape_string($data['amount']);
        $amount_limit = isset($data['amount_limit']) ? $conn->real_escape_string($data['amount_limit']) : null;
        $attendance_required = isset($data['attendance_required']) ? $conn->real_escape_string($data['attendance_required']) : 'No';
        $item_list = isset($data['item_list']) ? $conn->real_escape_string($data['item_list']) : null;
        $approval_required = isset($data['approval_required']) ? $conn->real_escape_string($data['approval_required']) : 'No';
        $requesting_account = isset($data['requesting_account']) ? $conn->real_escape_string($data['requesting_account']) : null;
        $approval_account = isset($data['approval_account']) ? $conn->real_escape_string($data['approval_account']) : null;

        // Get the highest numeric part from existing request_id (e.g. REQ-001 → 1)
        $result = $conn->query("SELECT MAX(CAST(SUBSTRING(request_id, 5) AS UNSIGNED)) AS last_number FROM budget_requests");

        $nextNumber = 1; // default

        if ($result) {
            $row = $result->fetch_assoc();
            if ($row && $row['last_number'] !== null) {
                $nextNumber = (int)$row['last_number'] + 1;
            }
        } else {
            error_log("[budget_requests_api.php DEBUG] SQL error while fetching max request_id: " . $conn->error);
        }

        // Format as REQ-001, REQ-002, etc.
        $request_id = "REQ-" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);

        // Use prepared statement for safer insert including new fields
        $stmt = $conn->prepare("INSERT INTO budget_requests (request_id, department, purpose, amount, amount_limit, attendance_required, item_list, approval_required, requesting_account, approval_account) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssddsssss', $request_id, $department, $purpose, $amount, $amount_limit, $attendance_required, $item_list, $approval_required, $requesting_account, $approval_account);
        error_log("[budget_requests_api.php DEBUG] Executing prepared insert for $request_id");

        if ($stmt->execute()) {
            error_log("[budget_requests_api.php DEBUG] Insert success for $request_id");
            echo json_encode(["success" => true, "message" => "Request added successfully"]);
        } else {
            error_log("[budget_requests_api.php ERROR] Insert failed: " . $stmt->error);
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        $stmt->close();
        break;

    case "PUT":
        error_log("[budget_requests_api.php DEBUG] PUT request triggered");
        $data = json_decode(file_get_contents("php://input"), true);
        error_log("[budget_requests_api.php DEBUG] Raw input: " . json_encode($data));

        $id = $conn->real_escape_string($data['id']);
        $department = $conn->real_escape_string($data['department']);
        $purpose = $conn->real_escape_string($data['purpose']);
        $amount = $conn->real_escape_string($data['amount']);
        $status = $conn->real_escape_string($data['status']);
        $amount_limit = isset($data['amount_limit']) ? $conn->real_escape_string($data['amount_limit']) : null;
        $attendance_required = isset($data['attendance_required']) ? $conn->real_escape_string($data['attendance_required']) : 'No';
        $item_list = isset($data['item_list']) ? $conn->real_escape_string($data['item_list']) : null;
        $approval_required = isset($data['approval_required']) ? $conn->real_escape_string($data['approval_required']) : 'No';
        $requesting_account = isset($data['requesting_account']) ? $conn->real_escape_string($data['requesting_account']) : null;
        $approval_account = isset($data['approval_account']) ? $conn->real_escape_string($data['approval_account']) : null;

        // If trying to approve, ensure current user is authorized (account_type == 1)
        if ($status === "Approved") {
            if (!isset($_SESSION['id'])) {
                echo json_encode(["success" => false, "error" => "Not authenticated to approve"]);
                exit;
            }
            $currentUserId = intval($_SESSION['id']);
            $uStmt = $conn->prepare("SELECT account_type FROM users WHERE id = ?");
            $uStmt->bind_param('i', $currentUserId);
            $uStmt->execute();
            $uRes = $uStmt->get_result();
            if (!$uRes || $uRes->num_rows === 0) {
                echo json_encode(["success" => false, "error" => "Cannot verify user account"]);
                exit;
            }
            $uRow = $uRes->fetch_assoc();
            $uStmt->close();
            if (!isset($uRow['account_type']) || intval($uRow['account_type']) !== 1) {
                echo json_encode(["success" => false, "error" => "Not authorized to approve"]);
                exit;
            }
        }

        // Use prepared statement to update
        $stmt = $conn->prepare("UPDATE budget_requests SET department=?, purpose=?, amount=?, amount_limit=?, attendance_required=?, item_list=?, approval_required=?, requesting_account=?, approval_account=?, status=? WHERE id=?");
        $stmt->bind_param('ssddssssssi', $department, $purpose, $amount, $amount_limit, $attendance_required, $item_list, $approval_required, $requesting_account, $approval_account, $status, $id);
        error_log("[budget_requests_api.php DEBUG] Executing prepared update for ID: $id");

        if ($stmt->execute()) {
            if ($status === "Approved") {
                $insertPlan = "INSERT INTO planning (request_id, department, purpose, amount, approved_at)
                               SELECT request_id, department, purpose, amount, NOW() 
                               FROM budget_requests WHERE id=$id";
                $conn->query($insertPlan);
                error_log("[budget_requests_api.php DEBUG] Status approved. Planning entry added.");
            }
            echo json_encode(["success" => true, "message" => "Request updated successfully"]);
        } else {
            error_log("[budget_requests_api.php ERROR] Update failed: " . $stmt->error);
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        $stmt->close();
        break;

    case "DELETE":
        error_log("[budget_requests_api.php DEBUG] DELETE request triggered");
        parse_str(file_get_contents("php://input"), $data);
        $id = $conn->real_escape_string($data['id']);
        error_log("[budget_requests_api.php DEBUG] Deleting ID: " . $id);

        if ($conn->query("DELETE FROM budget_requests WHERE id=$id")) {
            error_log("[budget_requests_api.php DEBUG] Deleted ID: $id successfully");
            echo json_encode(["success" => true, "message" => "Request deleted successfully"]);
        } else {
            error_log("[budget_requests_api.php ERROR] Delete failed: " . $conn->error);
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
        break;

    default:
        error_log("[budget_requests_api.php ERROR] Unsupported method: $method");
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>