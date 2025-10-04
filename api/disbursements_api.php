<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
include "db.php"; // adjust path to your db connection

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case "GET":
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
    break;

  case "POST":
    $data = json_decode(file_get_contents("php://input"), true);

    // Auto-generate Voucher No
    $res = $conn->query("SELECT COUNT(*) as count FROM disbursements");
    $count = $res->fetch_assoc()['count'] + 1;
    $voucher_no = "VCH-" . str_pad($count, 3, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO disbursements (voucher_no, vendor, category, amount, status, disbursement_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $voucher_no, $data['vendor'], $data['category'], $data['amount'], $data['status'], $data['disbursement_date']);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "voucher_no" => $voucher_no]);
    } else {
      echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    break;

  case "PUT":
    $data = json_decode(file_get_contents("php://input"), true);
    $id   = intval($data['id']);

    // Build dynamic SQL for updating only provided fields
    $fields = [];
    $params = [];
    $types  = "";

    if (isset($data['vendor'])) {
        $fields[] = "vendor=?";
        $params[] = $data['vendor'];
        $types   .= "s";
    }
    if (isset($data['category'])) {
        $fields[] = "category=?";
        $params[] = $data['category'];
        $types   .= "s";
    }
    if (isset($data['amount'])) {
        $fields[] = "amount=?";
        $params[] = $data['amount'];
        $types   .= "d"; // numeric
    }
    if (isset($data['status'])) {
        $fields[] = "status=?";
        $params[] = $data['status'];
        $types   .= "s";
    }
    if (isset($data['disbursement_date'])) {
        $fields[] = "disbursement_date=?";
        $params[] = $data['disbursement_date'];
        $types   .= "s";
    }

    if (!empty($fields)) {
        $sql = "UPDATE disbursements SET " . implode(", ", $fields) . " WHERE id=?";
        $stmt = $conn->prepare($sql);

        $types .= "i";          // last param is id
        $params[] = $id;
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $status = $data['status'] ?? null;

            // ✅ Notification
            $msg  = "Disbursement #$id updated";
            if ($status) $msg .= ". Status: $status";
            $link = "disbursements.php?id=" . $id;

            $notif_sql = "INSERT INTO notifications (module, record_id, message, link) 
                          VALUES ('disbursements', '$id', '$msg', '$link')";
            $conn->query($notif_sql);

            // Journal entry if Released
            if ($status === "Released") {
                // Fetch latest disbursement details (in case frontend only sent id + status)
                $res = $conn->query("SELECT vendor, amount FROM disbursements WHERE id=$id");
                $row = $res->fetch_assoc();

                $entry_date  = date("Y-m-d");
                $account     = "Cash";
                $description = "Disbursement #$id released to " . ($row['vendor'] ?? "Vendor");
                $amount      = floatval($row['amount']);
                $module      = "disbursements";
                $ref_id      = $id;

                $jstmt = $conn->prepare("INSERT INTO journal_entries 
                    (entry_date, account, description, debit, source_module, reference_id) 
                    VALUES (?, ?, ?, ?, ?, ?)");
                $jstmt->bind_param("sssdis", $entry_date, $account, $description, $amount, $module, $ref_id);

                if ($jstmt->execute()) {
                    // success
                } else {
                    error_log("Journal insert failed: " . $jstmt->error);
                }
            }

            echo json_encode([
                "success" => true,
                "message" => "Disbursement updated successfully"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error"   => $stmt->error ?: "No record updated"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "error"   => "No valid fields to update"
        ]);
    }
    break;

  case "DELETE":
    parse_str(file_get_contents("php://input"), $data);
    $stmt = $conn->prepare("DELETE FROM disbursements WHERE id=?");
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
      echo json_encode(["success" => true]);
    } else {
      echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    break;
}
?>
