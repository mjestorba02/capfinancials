<?php
header("Content-Type: application/json");
include "db.php";

$sql = "SELECT DISTINCT vendor FROM payments ORDER BY vendor ASC";
$result = $conn->query($sql);

$vendors = [];
while ($row = $result->fetch_assoc()) {
  $vendors[] = $row['vendor'];
}

echo json_encode($vendors);
?>
