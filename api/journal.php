<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include "db.php"; // adjust path to your db connection

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ==================== GET ====================
    case "GET":
        $result = $conn->query("SELECT 
            id AS entry_no, 
            account, 
            description, 
            credit,
            debit,
            entry_date, 
            source_module, 
            reference_id
        FROM journal_entries 
        ORDER BY entry_date DESC");

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode($rows);
        break;

    // ==================== POST ====================
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode(["success" => false, "error" => "Invalid input"]);
            exit;
        }

        $account     = $conn->real_escape_string($data["account"]);
        $type        = $conn->real_escape_string($data["type"]);
        $credit      = floatval($data["credit"]);
        $debit      = floatval($data["debit"]);
        $description = $conn->real_escape_string($data["description"]);
        $date        = $conn->real_escape_string($data["date"]);

        $sql = "INSERT INTO journal_entries (account, type, credit, debit, description, date)
                VALUES ('$account', '$type', $credit, $debit, '$description', '$date')";

        if ($conn->query($sql)) {
            echo json_encode(["success" => true, "message" => "Entry added"]);
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
        break;

    // ==================== PUT ====================
    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["entry_no"])) {
            echo json_encode(["success" => false, "error" => "Missing entry_no"]);
            exit;
        }

        $id            = intval($data["entry_no"]);
        $account       = $conn->real_escape_string($data["account"]);
        $description   = $conn->real_escape_string($data["description"]);
        $credit        = floatval($data["credit"]);
        $debit        = floatval($data["debit"]);
        $entry_date    = $conn->real_escape_string($data["entry_date"]);
        $source_module = $conn->real_escape_string($data["source_module"]);
        $reference_id  = $conn->real_escape_string($data["reference_id"]);

        $sql = "UPDATE journal_entries
                SET account='$account', description='$description', credit=$credit, debit=$debit, entry_date='$entry_date', source_module='$source_module', reference_id='$reference_id'
                WHERE id=$id";

        if ($conn->query($sql)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
        break;

    // ==================== DELETE ====================
    case "DELETE":
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(["success" => false, "error" => "Missing id"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM journal_entries WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        exit;

    // ==================== DEFAULT ====================
    default:
        echo json_encode(["success" => false, "error" => "Invalid request method"]);
        break;
}
?>