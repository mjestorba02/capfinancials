<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include "db.php"; // adjust to your db connection
$method = $_SERVER['REQUEST_METHOD'];

// Allow OPTIONS preflight for CORS
if ($method === "OPTIONS") {
  http_response_code(200);
  exit();
}

switch ($method) {
  // ================= GET =================
  case "GET":
    if (isset($_GET['id'])) {
      $stmt = $conn->prepare("SELECT * FROM disbursements WHERE id=?");
      $stmt->bind_param("i", $_GET['id']);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc();
      echo json_encode($result ?: ["error" => "Not found"]);
    } else {
      $result = $conn->query("SELECT * FROM disbursements ORDER BY id DESC");
      echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    }
    break;

  // ================= POST =================
  case "POST":
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
      echo json_encode(["success" => false, "error" => "Invalid JSON input"]);
      exit;
    }

    // Validate required fields
    $required = ["vendor", "category", "amount", "status", "disbursement_date"];
    foreach ($required as $field) {
      if (empty($data[$field])) {
        echo json_encode(["success" => false, "error" => "Missing field: $field"]);
        exit;
      }
    }

    // Auto-generate voucher number
    $res = $conn->query("SELECT COUNT(*) AS count FROM disbursements");
    $count = ($res && $res->num_rows) ? $res->fetch_assoc()['count'] + 1 : 1;
    $voucher_no = "VCH-" . str_pad($count, 3, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("
      INSERT INTO disbursements (voucher_no, vendor, category, amount, status, disbursement_date)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
      "sssiss",
      $voucher_no,
      $data['vendor'],
      $data['category'],
      $data['amount'],
      $data['status'],
      $data['disbursement_date']
    );

    if ($stmt->execute()) {
      echo json_encode([
        "success" => true,
        "message" => "Disbursement added successfully",
        "voucher_no" => $voucher_no
      ]);
    } else {
      echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    break;

  // ================= PUT =================
  case "PUT":
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || empty($data['id'])) {
      echo json_encode(["success" => false, "error" => "Invalid input or missing ID"]);
      exit;
    }

    $id = intval($data['id']);
    $fields = [];
    $params = [];
    $types = "";

    // Build dynamic update fields
    $map = [
      "vendor" => "s",
      "category" => "s",
      "amount" => "d",
      "status" => "s",
      "disbursement_date" => "s"
    ];

    foreach ($map as $key => $type) {
      if (isset($data[$key])) {
        $fields[] = "$key=?";
        $params[] = $data[$key];
        $types .= $type;
      }
    }

    if (empty($fields)) {
      echo json_encode(["success" => false, "error" => "No fields to update"]);
      exit;
    }

    $sql = "UPDATE disbursements SET " . implode(", ", $fields) . " WHERE id=?";
    $stmt = $conn->prepare($sql);
    $types .= "i";
    $params[] = $id;
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        $status = $data['status'] ?? null;

        // Insert notification
        $msg = "Disbursement #$id updated";
        if ($status) $msg .= " (Status: $status)";
        $link = "disbursements.php?id=" . $id;

        $notif_sql = $conn->prepare("
          INSERT INTO notifications (module, record_id, message, link)
          VALUES ('disbursements', ?, ?, ?)
        ");
        $notif_sql->bind_param("iss", $id, $msg, $link);
        $notif_sql->execute();

        // If released, add journal entry
        if ($status === "Released") {
          $res = $conn->query("SELECT vendor, amount FROM disbursements WHERE id=$id");
          if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $entry_date = date("Y-m-d");
            $account = "Cash";
            $description = "Disbursement #$id released to " . ($row['vendor'] ?? "Vendor");
            $amount = floatval($row['amount']);
            $module = "disbursements";
            $ref_id = $id;

            $jstmt = $conn->prepare("
              INSERT INTO journal_entries (entry_date, account, description, debit, source_module, reference_id)
              VALUES (?, ?, ?, ?, ?, ?)
            ");
            $jstmt->bind_param("sssdis", $entry_date, $account, $description, $amount, $module, $ref_id);
            $jstmt->execute();
          }
        }

        echo json_encode(["success" => true, "message" => "Disbursement updated successfully"]);
      } else {
        echo json_encode(["success" => false, "error" => "No record updated"]);
      }
    } else {
      echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    break;

  // ================= DELETE =================
  case "DELETE":
    parse_str(file_get_contents("php://input"), $data);
    if (empty($data['id'])) {
      echo json_encode(["success" => false, "error" => "Missing ID"]);
      exit;
    }

    $stmt = $conn->prepare("DELETE FROM disbursements WHERE id=?");
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "message" => "Deleted successfully"]);
    } else {
      echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    break;

  default:
    echo json_encode(["success" => false, "error" => "Unsupported method"]);
    break;
}
?>