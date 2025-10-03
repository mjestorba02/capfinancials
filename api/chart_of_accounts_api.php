<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // database connection

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ===================== GET =====================
    case "GET":
        if (isset($_GET['id'])) {
            // Get single account
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM chart_of_accounts WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result);
        } else {
            // Get all accounts
            $sql = "SELECT * FROM chart_of_accounts ORDER BY account_code ASC";
            $result = $conn->query($sql);
            $accounts = [];
            while ($row = $result->fetch_assoc()) {
                $accounts[] = $row;
            }
            echo json_encode($accounts);
        }
        break;

    // ===================== POST =====================
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        $code  = $data['account_code'] ?? '';
        $name  = $data['account_name'] ?? '';
        $cat   = $data['category'] ?? '';
        $atype = $data['account_type'] ?? '';
        $desc  = $data['description'] ?? '';
        $bal   = isset($data['balance']) ? floatval($data['balance']) : 0;

        // Required fields
        if (!$code || !$name || !$cat || !$atype) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing required fields"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO chart_of_accounts 
            (account_code, account_name, account_type, category, description, balance) 
            VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Prepare failed", "error" => $conn->error]);
            exit;
        }

        $stmt->bind_param("sssssd", $code, $name, $atype, $cat, $desc, $bal);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Account added successfully", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error adding account", "error" => $stmt->error]);
        }
        break;

    // ===================== PUT =====================
    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        $id   = $data['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Account ID is required"]);
            exit;
        }

        // Merge values (if not provided, fallback to existing record)
        $sel = $conn->prepare("SELECT * FROM chart_of_accounts WHERE id=?");
        $sel->bind_param("i", $id);
        $sel->execute();
        $existing = $sel->get_result()->fetch_assoc();

        if (!$existing) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Account not found"]);
            exit;
        }

        $code  = $data['account_code'] ?? $existing['account_code'];
        $name  = $data['account_name'] ?? $existing['account_name'];
        $atype = $data['account_type'] ?? $existing['account_type'];
        $cat   = $data['category'] ?? $existing['category'];
        $desc  = $data['description'] ?? $existing['description'];
        $bal   = isset($data['balance']) ? floatval($data['balance']) : floatval($existing['balance']);

        $stmt = $conn->prepare("UPDATE chart_of_accounts 
            SET account_code=?, account_name=?, account_type=?, category=?, description=?, balance=? 
            WHERE id=?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Prepare failed", "error" => $conn->error]);
            exit;
        }

        $stmt->bind_param("sssssdi", $code, $name, $atype, $cat, $desc, $bal, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Account updated successfully", "rows_affected" => $stmt->affected_rows]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error updating account", "error" => $stmt->error]);
        }
        break;

    // ===================== DELETE =====================
    case "DELETE":
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Account ID is required"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM chart_of_accounts WHERE id = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Prepare failed", "error" => $conn->error]);
            exit;
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Account deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error deleting account", "error" => $stmt->error]);
        }
        break;

    // ===================== DEFAULT =====================
    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
        break;
}
?>