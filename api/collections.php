<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
include "db.php"; // adjust path to your db connection

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ===================== GET =====================
    case "GET":
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM collections WHERE invoice_no=?");
            $stmt->bind_param("s", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result);
        } else {
            $result = $conn->query("SELECT * FROM collections ORDER BY id DESC");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
        break;

    // ===================== POST =====================
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        // Auto-generate Invoice No if not provided
        if (empty($data['invoice_no'])) {
            $res = $conn->query("SELECT COUNT(*) as count FROM collections");
            $count = $res->fetch_assoc()['count'] + 1;
            $data['invoice_no'] = "INV-" . str_pad($count, 3, "0", STR_PAD_LEFT);
        }

        $stmt = $conn->prepare("INSERT INTO collections (invoice_no, customer, department, amount, status, date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssss",
            $data['invoice_no'],
            $data['customer'],
            $data['department'],
            $data['amount'],
            $data['status'],
            $data['date']
        );

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "invoice_no" => $data['invoice_no']]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        break;
    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['invoice_no'])) {
            echo json_encode(["success"=>false,"error"=>"Invoice number required"]);
            exit;
        }

        $invoice_no = $data['invoice_no'];

        // Fetch existing record first
        $res = $conn->prepare("SELECT customer, department, amount, status, date, id FROM collections WHERE invoice_no=?");
        $res->bind_param("s", $invoice_no);
        $res->execute();
        $res->bind_result($db_customer, $db_department, $db_amount, $db_status, $db_date, $db_id);
        $res->fetch();
        $res->close();

        // If no record found
        if (!$db_customer && !$db_department && !$db_amount) {
            echo json_encode(["success"=>false,"error"=>"Invoice not found"]);
            exit;
        }

        // Use incoming values if provided, else keep existing
        $customer   = $data['customer']   ?? $db_customer;
        $department = $data['department'] ?? $db_department;
        $amount     = isset($data['amount']) ? floatval($data['amount']) : $db_amount;
        $status     = $data['status']     ?? $db_status;
        $date       = $data['date']       ?? $db_date;
        $db_id      = $data['id']         ?? $db_id;

        // Update query
        $stmt = $conn->prepare("UPDATE collections 
                                SET customer=?, department=?, amount=?, status=?, date=? 
                                WHERE invoice_no=?");
        $stmt->bind_param("ssdsss", $customer, $department, $amount, $status, $date, $invoice_no);
        $updateSuccess = $stmt->execute();

        // If status = paid, create journal entry
        if ($status === "Paid") {
            $account = "Accounts Receivable";
            $description = "Payment approved for Invoice #$invoice_no from $customer";
            $entry_date = date("Y-m-d");

            $jstmt = $conn->prepare("INSERT INTO journal_entries 
                                    (entry_date, account, description, credit, source_module, reference_id) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $module = "collections";
            $jstmt->bind_param("sssiss", $entry_date, $account, $description, $amount, $module, $invoice_no);
            $jstmt->execute();
        }

        // 🔹 Notifications
        $notif_stmt = @$conn->prepare(
            "INSERT INTO notifications (module, record_id, message, link) VALUES (?, ?, ?, ?)"
        );
        if ($notif_stmt) {
            $module = 'collections';
            $record_id = $db_id;
            $msg = "Collection #$invoice_no updated. Status: $status";
            $link = "sales_invoices.php?invoice_no=" . urlencode($invoice_no);
            @$notif_stmt->bind_param("ssss", $module, $record_id, $msg, $link);
            @$notif_stmt->execute();
        }

        echo json_encode([
            "success" => true,
            "message" => $updateSuccess ? "Collection updated successfully" : "Collection update attempted"
        ]);
        break;


}
