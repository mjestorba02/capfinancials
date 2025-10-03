<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if(isset($_GET['id'])) {
            // Fetch single planning entry
            $id = $conn->real_escape_string($_GET['id']);
            $res = $conn->query("SELECT * FROM planning WHERE id=$id");
            echo json_encode($res->fetch_assoc());
        } else {
            // Fetch all planning entries
            $res = $conn->query("SELECT * FROM planning ORDER BY approved_at DESC");
            $planning = [];
            while($row = $res->fetch_assoc()) $planning[] = $row;
            echo json_encode($planning);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $request_id = $conn->real_escape_string($data['request_id']);
        $department = $conn->real_escape_string($data['department']);
        $purpose = $conn->real_escape_string($data['purpose']);
        $amount = $conn->real_escape_string($data['amount']);

        $sql = "INSERT INTO planning (request_id, department, purpose, amount, approved_at)
                VALUES ('$request_id', '$department', '$purpose', '$amount', NOW())";

        if($conn->query($sql)) {
            echo json_encode(["success"=>true, "message"=>"Planning entry added successfully"]);
        } else {
            echo json_encode(["success"=>false, "error"=>$conn->error]);
        }
        break;

    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $conn->real_escape_string($data['id']);
        $department = $conn->real_escape_string($data['department']);
        $purpose = $conn->real_escape_string($data['purpose']);
        $amount = $conn->real_escape_string($data['amount']);

        $sql = "UPDATE planning SET department='$department', purpose='$purpose', amount='$amount' WHERE id=$id";

        if($conn->query($sql)) {
            echo json_encode(["success"=>true, "message"=>"Planning entry updated successfully"]);
        } else {
            echo json_encode(["success"=>false, "error"=>$conn->error]);
        }
        break;

    case "DELETE":
        parse_str(file_get_contents("php://input"), $data);
        $id = $conn->real_escape_string($data['id']);

        if($conn->query("DELETE FROM planning WHERE id=$id")) {
            echo json_encode(["success"=>true, "message"=>"Planning entry deleted successfully"]);
        } else {
            echo json_encode(["success"=>false, "error"=>$conn->error]);
        }
        break;

    default:
        echo json_encode(["success"=>false, "message"=>"Method not allowed"]);
}
?>
