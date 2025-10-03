<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $res = $conn->query("SELECT * FROM budget_requests ORDER BY created_at DESC");
        $requests = [];
        while ($row = $res->fetch_assoc()) $requests[] = $row;
        echo json_encode($requests);
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $department = $conn->real_escape_string($data['department']);
        $purpose = $conn->real_escape_string($data['purpose']);
        $amount = $conn->real_escape_string($data['amount']);

        $count = $conn->query("SELECT COUNT(*) as count FROM budget_requests")->fetch_assoc()['count'] + 1;
        $request_id = "REQ-" . str_pad($count, 3, "0", STR_PAD_LEFT);

        $sql = "INSERT INTO budget_requests (request_id, department, purpose, amount) 
                VALUES ('$request_id', '$department', '$purpose', '$amount')";
        if ($conn->query($sql)) echo json_encode(["success"=>true,"message"=>"Request added successfully"]);
        else echo json_encode(["success"=>false,"error"=>$conn->error]);
        break;

    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $conn->real_escape_string($data['id']);
        $department = $conn->real_escape_string($data['department']);
        $purpose = $conn->real_escape_string($data['purpose']);
        $amount = $conn->real_escape_string($data['amount']);
        $status = $conn->real_escape_string($data['status']);

        $sql = "UPDATE budget_requests SET department='$department', purpose='$purpose', amount='$amount', status='$status' WHERE id=$id";

        if ($conn->query($sql)) {
            if ($status === "Approved") {
                $conn->query("INSERT INTO planning (request_id, department, purpose, amount, approved_at)
                              SELECT request_id, department, purpose, amount, NOW() 
                              FROM budget_requests WHERE id=$id");
            }
            echo json_encode(["success"=>true,"message"=>"Request updated successfully"]);
        } else echo json_encode(["success"=>false,"error"=>$conn->error]);
        break;

    case "DELETE":
        parse_str(file_get_contents("php://input"), $data);
        $id = $conn->real_escape_string($data['id']);
        if ($conn->query("DELETE FROM budget_requests WHERE id=$id")) echo json_encode(["success"=>true,"message"=>"Request deleted successfully"]);
        else echo json_encode(["success"=>false,"error"=>$conn->error]);
        break;

    default:
        echo json_encode(["success"=>false,"message"=>"Method not allowed"]);
}
?>
