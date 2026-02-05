<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include "db.php";

// 🔹 Log errors to a custom file
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error_log.txt");
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

// Simple function to log custom messages
function logMsg($msg) {
    error_log("[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", 3, __DIR__ . "/error_log.txt");
}

function getArchivedDisbursementById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM archived_disbursements WHERE id = ?");
    if (!$stmt) return null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

switch ($method) {
  case "OPTIONS":
    http_response_code(200);
    echo json_encode(["success" => true]);
    break;

  case "GET":
    try {
      if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM archived_disbursements WHERE id=?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        echo json_encode($result);
      } else {
        $result = $conn->query("SELECT * FROM archived_disbursements ORDER BY archived_at DESC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
      }
    } catch (Exception $e) {
      logMsg("GET error: " . $e->getMessage());
      echo json_encode(["success" => false, "error" => "Failed to fetch archived disbursements"]);
    }
    break;

  case "POST":
    try {
      $raw = file_get_contents("php://input");
      $data = json_decode($raw, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
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

      // Check if this is a retrieve request
      if (isset($data['action']) && $data['action'] === 'retrieve') {
        if (!isset($data['id'])) {
          echo json_encode(["success" => false, "error" => "Missing ID"]);
          break;
        }

        $id = intval($data['id']);

        // Fetch the archived record
        $stmt = $conn->prepare("SELECT * FROM archived_disbursements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $record = $result->fetch_assoc();

        if (!$record) {
          echo json_encode(["success" => false, "error" => "Archived record not found"]);
          break;
        }

        // Update the original disbursements record to mark as not archived (unarchive)
        $restore_stmt = $conn->prepare(
          "UPDATE disbursements SET is_archived = 0, archived_at = NULL WHERE id = ?"
        );

        if (!$restore_stmt) {
          logMsg("Restore prepare failed: " . $conn->error);
          echo json_encode(["success" => false, "error" => "Restore prepare failed: " . $conn->error]);
          break;
        }

        $restore_stmt->bind_param("i", $id);

        if (!$restore_stmt->execute()) {
          logMsg("Restore execute failed: " . $restore_stmt->error);
          echo json_encode(["success" => false, "error" => "Failed to restore record: " . $restore_stmt->error]);
          break;
        }

        // Delete from archived_disbursements table
        $delete_stmt = $conn->prepare("DELETE FROM archived_disbursements WHERE id = ?");
        if ($delete_stmt) {
          $delete_stmt->bind_param("i", $id);
          $delete_stmt->execute();
        }

        // Insert notification
        try {
          $msg = "Disbursement #" . $record['voucher_no'] . " has been retrieved from archive";
          $link = "disbursement.php?id=" . $id;
          $notif_stmt = $conn->prepare("INSERT INTO notifications (message, link) VALUES (?, ?)");
          if ($notif_stmt) {
            $notif_stmt->bind_param("ss", $msg, $link);
            $notif_stmt->execute();
          }
        } catch (Throwable $t) {
          logMsg("Notification insert failed: " . $t->getMessage());
        }

        echo json_encode(["success" => true, "message" => "Disbursement retrieved successfully"]);
        break;
      }

      echo json_encode(["success" => false, "error" => "Invalid action"]);
    } catch (Exception $e) {
      logMsg("POST exception: " . $e->getMessage());
      echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    break;

  case "DELETE":
    try {
      parse_str(file_get_contents("php://input"), $data);
      
      if (!isset($data['id'])) {
        echo json_encode(["success" => false, "error" => "Missing ID"]);
        break;
      }

      $id = intval($data['id']);

      // Permanently delete from archived table
      $stmt = $conn->prepare("DELETE FROM archived_disbursements WHERE id=?");
      $stmt->bind_param("i", $id);

      if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Record permanently deleted"]);
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
