<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Suppress PHP errors from displaying and instead log them
error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error_log.txt");

// Set error handler to catch and log errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("[" . date("Y-m-d H:i:s") . "] Error: $errstr in $errfile:$errline\n", 3, __DIR__ . "/error_log.txt");
    // Return true to prevent default PHP error handler from running
    return true;
});

// Catch fatal errors that slip through
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("[" . date("Y-m-d H:i:s") . "] FATAL: " . $error['message'] . " in " . $error['file'] . ":" . $error['line'] . "\n", 3, __DIR__ . "/error_log.txt");
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Fatal error: " . $error['message']]);
        exit;
    }
});

include "db.php";

$method = $_SERVER['REQUEST_METHOD'];

// Simple function to log custom messages
function logMsg($msg) {
    error_log("[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", 3, __DIR__ . "/error_log.txt");
}

function getDisbursementById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM disbursements WHERE id = ?");
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
        $stmt = $conn->prepare("SELECT * FROM disbursements WHERE id=? AND is_archived=0");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        echo json_encode($result);
      } else {
        $result = $conn->query("SELECT * FROM disbursements WHERE is_archived=0 ORDER BY id DESC");
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

      // Check if this is an archive request
      if (isset($data['action']) && $data['action'] === 'archive') {
        try {
          // Archive disbursement
          if (!isset($data['id'])) {
            echo json_encode(["success" => false, "error" => "Missing ID"]);
            break;
          }

          $id = intval($data['id']);

          // Check if archived_disbursements table exists
          $check_table = $conn->query("SHOW TABLES LIKE 'archived_disbursements'");
          if (!$check_table || $check_table->num_rows === 0) {
            logMsg("archived_disbursements table does not exist");
            echo json_encode(["success" => false, "error" => "Archive system not initialized. Please run SQL migration: 📌_RUN_THIS_SQL.txt in financial2 root folder."]);
            break;
          }

          // Fetch the disbursement record
          $stmt = $conn->prepare("SELECT * FROM disbursements WHERE id = ?");
          if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
          }
          $stmt->bind_param("i", $id);
          $stmt->execute();
          $result = $stmt->get_result();
          $record = $result->fetch_assoc();

          if (!$record) {
            echo json_encode(["success" => false, "error" => "Disbursement not found"]);
            break;
          }

          // Insert into archived_disbursements table
          $archive_stmt = $conn->prepare(
            "INSERT INTO archived_disbursements (id, voucher_no, vendor, category, amount, status, disbursement_date, created_at, archived_by, archive_reason, module) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
          );
          
          if (!$archive_stmt) {
            throw new Exception("Archive prepare failed: " . $conn->error);
          }

          $archived_by = $_SESSION['user_id'] ?? 'System';
          $archive_reason = $data['reason'] ?? '';
          $module = 'disbursements'; // Add module field

          // Bind parameters - 11 variables need 11 type characters
          if (!$archive_stmt->bind_param(
            "isssdssssss",
            $record['id'],
            $record['voucher_no'],
            $record['vendor'],
            $record['category'],
            $record['amount'],
            $record['status'],
            $record['disbursement_date'],
            $record['created_at'],
            $archived_by,
            $archive_reason,
            $module
          )) {
            throw new Exception("Bind param failed: " . $archive_stmt->error);
          }

          if (!$archive_stmt->execute()) {
            throw new Exception("Archive execute failed: " . $archive_stmt->error);
          }

          // Update original record to mark as archived
          $update_stmt = $conn->prepare("UPDATE disbursements SET is_archived = 1, archived_at = NOW() WHERE id = ?");
          if (!$update_stmt) {
            throw new Exception("Update prepare failed: " . $conn->error);
          }
          
          $update_stmt->bind_param("i", $id);

          if (!$update_stmt->execute()) {
            throw new Exception("Archive update failed: " . $update_stmt->error);
          }

          // Insert notification
          $msg = "Disbursement #" . $record['voucher_no'] . " has been archived";
          $link = "disbursement.php";
          $notif_stmt = $conn->prepare("INSERT INTO notifications (message, link) VALUES (?, ?)");
          if ($notif_stmt) {
            $notif_stmt->bind_param("ss", $msg, $link);
            $notif_stmt->execute();
          }

          echo json_encode(["success" => true, "message" => "Disbursement archived successfully"]);
          break;
        } catch (Exception $archive_error) {
          logMsg("Archive error: " . $archive_error->getMessage());
          echo json_encode(["success" => false, "error" => $archive_error->getMessage()]);
          break;
        }
      }

      // Original create disbursement logic
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

    $fields = [];
    $params = [];
    $types = "";

    if (isset($data['vendor'])) {
        $fields[] = "vendor=?";
        $params[] = $data['vendor'];
        $types .= "s";
    }
    if (isset($data['category'])) {
        $fields[] = "category=?";
        $params[] = $data['category'];
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
    if (isset($data['disbursement_date'])) {
        $fields[] = "disbursement_date=?";
        $params[] = $data['disbursement_date'];
        $types .= "s";
    }

    if (empty($fields)) {
        echo json_encode(["success" => false, "stage" => "fields_check", "error" => "No fields to update"]);
        break;
    }

    $sql = "UPDATE disbursements SET " . implode(", ", $fields) . " WHERE id=?";
    $params[] = $id;
    $types .= "i";

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

    error_log("DEBUG: Disbursement update OK, ID = $id");

    $warnings = [];

    // Insert notification
    try {
        $msg = "Disbursement #$id updated";
        $link = "disbursement.php?id=" . $id;
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

    // Fetch updated record
    $current = getDisbursementById($conn, $id);
    $effectiveAmount = isset($data['amount']) ? floatval($data['amount']) : (float)($current['amount'] ?? 0);
    $effectiveVendor = isset($data['vendor']) ? $data['vendor'] : ($current['vendor'] ?? '');
    $effectiveDate = isset($data['disbursement_date']) ? $data['disbursement_date'] : ($current['disbursement_date'] ?? date('Y-m-d'));

    if (isset($data['status']) && $data['status'] === "Released") {
        try {
            $sql_journal = "INSERT INTO journal_entries 
                (entry_date, account, description, debit, credit, source_module, reference_id)
                VALUES (NOW(), ?, ?, ?, ?, 'Disbursements', ?)";
            $journal_stmt = $conn->prepare($sql_journal);

            if (!$journal_stmt) {
                $warnings[] = "Journal prepare failed: " . $conn->error;
            } else {
                // Credit
                $account2 = "Disbursement";
                $desc2 = "Disbursement paid to vendor " . $effectiveVendor;
                $debit2 = $effectiveAmount;
                $credit2 = 0.00;

                if (!$journal_stmt->bind_param("ssddi", $account2, $desc2, $debit2, $credit2, $ref)) {
                    $warnings[] = "Journal bind (credit) failed: " . $journal_stmt->error;
                } elseif (!$journal_stmt->execute()) {
                    $warnings[] = "Journal execute (credit) failed: " . $journal_stmt->error;
                } else {
                    error_log("Journal Credit inserted for Disbursement ID $id, Amount: $credit2");
                }
            }
        } catch (Throwable $t) {
            $warnings[] = "Journal exception: " . $t->getMessage();
        }
    }

    echo json_encode([
        "success" => true,
        "stage" => "done",
        "message" => "Disbursement updated successfully",
        "id" => $id,
        "amount" => $effectiveAmount,
        "warnings" => $warnings
    ]);
    break;

  case "DELETE":
    try {
      $raw = file_get_contents("php://input");
      parse_str($raw, $data);
      
      if (!isset($data['id'])) {
        echo json_encode(["success" => false, "error" => "Missing ID"]);
        break;
      }

      $id = intval($data['id']);

      // Check if this is a permanent delete or a retrieval from archive
      if (isset($data['action']) && $data['action'] === 'retrieve') {
        // Retrieve from archived - move back to active disbursements
        $stmt = $conn->prepare("SELECT * FROM archived_disbursements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $archived_record = $result->fetch_assoc();

        if (!$archived_record) {
          echo json_encode(["success" => false, "error" => "Archived record not found"]);
          break;
        }

        // Re-insert into disbursements table
        $restore_stmt = $conn->prepare(
          "INSERT INTO disbursements (id, voucher_no, vendor, category, amount, status, disbursement_date, created_at, is_archived, archived_at) 
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NULL)"
        );

        if (!$restore_stmt) {
          logMsg("Restore prepare failed: " . $conn->error);
          echo json_encode(["success" => false, "error" => "Restore prepare failed"]);
          break;
        }

        $restore_stmt->bind_param(
          "issssds",
          $archived_record['id'],
          $archived_record['voucher_no'],
          $archived_record['vendor'],
          $archived_record['category'],
          $archived_record['amount'],
          $archived_record['status'],
          $archived_record['disbursement_date'],
          $archived_record['created_at']
        );

        if (!$restore_stmt->execute()) {
          logMsg("Restore execute failed: " . $restore_stmt->error);
          echo json_encode(["success" => false, "error" => "Failed to restore record"]);
          break;
        }

        // Insert notification
        try {
          $msg = "Disbursement #" . $archived_record['voucher_no'] . " has been retrieved from archive";
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
      } else {
        // Default DELETE - mark as archived in main table
        $stmt = $conn->prepare("DELETE FROM disbursements WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
          echo json_encode(["success" => true]);
        } else {
          logMsg("DELETE failed: " . $stmt->error);
          echo json_encode(["success" => false, "error" => "Delete failed"]);
        }
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