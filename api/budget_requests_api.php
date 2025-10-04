<?php

ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/budget_error.log");
error_log("=== Budget API hit: " . $_SERVER['REQUEST_METHOD'] . " ===");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

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
                WHERE department NOT IN (SELECT department FROM allocations)
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

        // Get the latest request_id (numerical part)
        $result = $conn->query("SELECT request_id FROM budget_requests ORDER BY id DESC LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            // Extract number from 'REQ-###'
            $lastNumber = (int) filter_var($row['request_id'], FILTER_SANITIZE_NUMBER_INT);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1; // start from REQ-001 if none exist
        }

        $request_id = "REQ-" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);

        $sql = "INSERT INTO budget_requests (request_id, department, purpose, amount)
                VALUES ('$request_id', '$department', '$purpose', '$amount')";
        error_log("[budget_requests_api.php DEBUG] Executing SQL: " . $sql);

        if ($conn->query($sql)) {
            error_log("[budget_requests_api.php DEBUG] Insert success for $request_id");
            echo json_encode(["success" => true, "message" => "Request added successfully"]);
        } else {
            error_log("[budget_requests_api.php ERROR] Insert failed: " . $conn->error);
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
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

        $sql = "UPDATE budget_requests 
                SET department='$department', purpose='$purpose', amount='$amount', status='$status' 
                WHERE id=$id";
        error_log("[budget_requests_api.php DEBUG] Executing SQL: " . $sql);

        if ($conn->query($sql)) {
            if ($status === "Approved") {
                $insertPlan = "INSERT INTO planning (request_id, department, purpose, amount, approved_at)
                               SELECT request_id, department, purpose, amount, NOW() 
                               FROM budget_requests WHERE id=$id";
                $conn->query($insertPlan);
                error_log("[budget_requests_api.php DEBUG] Status approved. Planning entry added.");
            }
            echo json_encode(["success" => true, "message" => "Request updated successfully"]);
        } else {
            error_log("[budget_requests_api.php ERROR] Update failed: " . $conn->error);
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
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