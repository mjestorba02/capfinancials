<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include "db.php";

// 🔹 Log errors to a custom file
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error_log.txt"); // log file in same directory
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

// Simple function to log custom messages
function logMsg($msg) {
    error_log("[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", 3, __DIR__ . "/error_log.txt");
}

switch ($method) {
  case "OPTIONS":
    http_response_code(200);
    echo json_encode(["success" => true]);
    break;
  case "GET":
    try {
      if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM disbursements WHERE id=?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        echo json_encode($result);
      } else {
        $result = $conn->query("SELECT * FROM disbursements ORDER BY id DESC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
      }
    } catch (Exception $e) {
      logMsg("GET error: " . $e->getMessage());
      echo json_encode(["success" => false, "error" => "Failed to fetch disbursements"]);
    }
    break;

  case "POST":
    try {
      $raw = file_get_contents("php://input");
      $data = json_decode($raw, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        // Fallback: allow x-www-form-urlencoded payloads
        $alt = [];
        parse_str($raw, $alt);
        if (!empty($alt)) {
          $data = $alt;
        }
      }

      if (!is_array($data)) {
        logMsg("POST invalid input. Raw body: " . $raw);
        echo json_encode(["success" => false, "error" => "Invalid JSON input"]);
        break;
      }

      // Basic validation
      $required = ["vendor", "category", "amount", "status", "disbursement_date"];
      foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === "") {
          echo json_encode(["success" => false, "error" => "Missing field: $field"]);
          break 2;
        }
      }

      $vendor = trim($data['vendor']);
      $category = trim($data['category']);
      $status = trim($data['status']);
      $amount = (float)$data['amount'];
      $date = trim($data['disbursement_date']);

      // Generate next voucher number based on the latest existing voucher
      $result = $conn->query("SELECT voucher_no FROM disbursements ORDER BY id DESC LIMIT 1");
      if ($result === false) {
          logMsg("POST voucher fetch failed: " . $conn->error);
          echo json_encode(["success" => false, "error" => "Failed to fetch latest voucher"]);
          break;
      }

      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $last_voucher = $row['voucher_no'];

          // Extract numeric part (e.g. from VCH-005 → 5)
          $num = (int)substr($last_voucher, 4);

          // Increment and format (e.g. 6 → VCH-006)
          $new_num = $num + 1;
          $voucher_no = "VCH-" . str_pad($new_num, 3, "0", STR_PAD_LEFT);
      } else {
          // No existing vouchers, start from VCH-001
          $voucher_no = "VCH-001";
      }

      // Insert new disbursement
      $stmt = $conn->prepare("INSERT INTO disbursements (voucher_no, vendor, category, amount, status, disbursement_date) VALUES (?, ?, ?, ?, ?, ?)");
      if (!$stmt) {
          logMsg("POST prepare failed: " . $conn->error);
          echo json_encode(["success" => false, "error" => "Insert prepare failed"]);
          break;
      }

      // Use correct types: sssdss (amount as double)
      $stmt->bind_param("sssdss", $voucher_no, $vendor, $category, $amount, $status, $date);

      if ($stmt->execute()) {
        echo json_encode(["success" => true, "voucher_no" => $voucher_no]);
      } else {
        logMsg("POST execute failed: " . $stmt->error);
        echo json_encode(["success" => false, "error" => "Insert failed: " . $stmt->error]);
      }
    } catch (Exception $e) {
      logMsg("POST exception: " . $e->getMessage());
      echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    break;

  case "PUT":
    try {
      $data = json_decode(file_get_contents("php://input"), true);
      if (!$data || !isset($data['id'])) throw new Exception("Missing ID or invalid JSON");

      $id = intval($data['id']);
      $fields = [];
      $params = [];
      $types  = "";

      if (isset($data['vendor'])) { $fields[] = "vendor=?"; $params[] = $data['vendor']; $types .= "s"; }
      if (isset($data['category'])) { $fields[] = "category=?"; $params[] = $data['category']; $types .= "s"; }
      if (isset($data['amount'])) { $fields[] = "amount=?"; $params[] = $data['amount']; $types .= "d"; }
      if (isset($data['status'])) { $fields[] = "status=?"; $params[] = $data['status']; $types .= "s"; }
      if (isset($data['disbursement_date'])) { $fields[] = "disbursement_date=?"; $params[] = $data['disbursement_date']; $types .= "s"; }

      if (!empty($fields)) {
        $sql = "UPDATE disbursements SET " . implode(", ", $fields) . " WHERE id=?";
        $stmt = $conn->prepare($sql);
        $types .= "i";
        $params[] = $id;
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
          if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true]);
          } else {
            logMsg("PUT no change: ID $id");
            echo json_encode(["success" => false, "error" => "No changes made"]);
          }
        } else {
          logMsg("PUT failed: " . $stmt->error);
          echo json_encode(["success" => false, "error" => "Update failed"]);
        }
      } else {
        logMsg("PUT invalid fields for ID $id");
        echo json_encode(["success" => false, "error" => "No valid fields to update"]);
      }
    } catch (Exception $e) {
      logMsg("PUT error: " . $e->getMessage());
      echo json_encode(["success" => false, "error" => "Server error"]);
    }
    break;

  case "DELETE":
    try {
      parse_str(file_get_contents("php://input"), $data);
      if (!isset($data['id'])) throw new Exception("Missing ID");

      $stmt = $conn->prepare("DELETE FROM disbursements WHERE id=?");
      $stmt->bind_param("i", $data['id']);

      if ($stmt->execute()) {
        echo json_encode(["success" => true]);
      } else {
        logMsg("DELETE failed: " . $stmt->error);
        echo json_encode(["success" => false, "error" => "Delete failed"]);
      }
    } catch (Exception $e) {
      logMsg("DELETE error: " . $e->getMessage());
      echo json_encode(["success" => false, "error" => "Server error"]);
    }
    break;

  default:
    logMsg("Invalid method: $method");
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>