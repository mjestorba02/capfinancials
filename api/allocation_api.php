<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case "GET":
        // Fetch all allocations
        $res = $conn->query("SELECT * FROM allocation ORDER BY created_at DESC");
        $allocations = [];
        while($row = $res->fetch_assoc()) $allocations[] = $row;
        echo json_encode($allocations);
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $request_id = intval($data['request_id']);
        $department = $conn->real_escape_string($data['department']);
        $project = $conn->real_escape_string($data['project']);
        $allocated = $conn->real_escape_string($data['allocated']);

        error_log("[allocation_api.php DEBUG] Received POST with request_id=$request_id, department=$department, project=$project, allocated=$allocated");

        // Insert allocation
        $sql = "INSERT INTO allocation (request_id, department, project, allocated) 
                VALUES ('$request_id', '$department', '$project', '$allocated')";
        error_log("[allocation_api.php DEBUG] Insert SQL: $sql");

        if ($conn->query($sql)) {
            error_log("[allocation_api.php DEBUG] Allocation insert successful. Now updating budget_requests...");

            // Mark request as approved
            $update = "UPDATE budget_requests SET status = 'Approved' WHERE id = $request_id";
            error_log("[allocation_api.php DEBUG] Update SQL: $update");

            if ($conn->query($update)) {
                error_log("[allocation_api.php DEBUG] budget_requests updated successfully (id=$request_id)");
            } else {
                error_log("[allocation_api.php ERROR] Failed to update budget_requests: " . $conn->error);
            }

            echo json_encode([
                "success" => true,
                "message" => "Allocation added successfully and request approved."
            ]);
        } else {
            error_log("[allocation_api.php ERROR] Failed to insert allocation: " . $conn->error);
            echo json_encode([
                "success" => false,
                "error" => $conn->error
            ]);
        }
        break;

    case "PUT":
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $conn->real_escape_string($data['id']);
    $used = isset($data['used']) ? $conn->real_escape_string($data['used']) : null;
    
    if ($used !== null) {
        $sql = "UPDATE allocation SET used='$used' WHERE id=$id";
        if($conn->query($sql)) {
            echo json_encode(["success"=>true, "message"=>"Used updated successfully"]);
        } else {
            echo json_encode(["success"=>false, "error"=>$conn->error]);
        }
    } else {
        echo json_encode(["success"=>false, "error"=>"No used amount provided"]);
    }
    break;


    case "DELETE":
        parse_str(file_get_contents("php://input"), $data);
        $id = $conn->real_escape_string($data['id']);
        if($conn->query("DELETE FROM allocation WHERE id=$id")) {
            echo json_encode(["success"=>true, "message"=>"Allocation deleted successfully"]);
        } else {
            echo json_encode(["success"=>false, "error"=>$conn->error]);
        }
        break;

    default:
        echo json_encode(["success"=>false,"message"=>"Method not allowed"]);
}
?>
