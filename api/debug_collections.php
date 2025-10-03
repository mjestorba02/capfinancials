<?php
header('Content-Type: application/json');
include 'db.php';

$response = [
    'ok' => false,
    'message' => '',
    'count' => 0,
    'sample' => [],
    'error' => null
];

// Check connection
if (!$conn) {
    $response['message'] = 'No DB connection';
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

try {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM collections");
    if ($res) {
        $cnt = $res->fetch_assoc()['cnt'];
        $response['count'] = intval($cnt);
    }

    $r2 = $conn->query("SELECT * FROM collections ORDER BY id DESC LIMIT 10");
    if ($r2) {
        $rows = [];
        while ($row = $r2->fetch_assoc()) {
            $rows[] = $row;
        }
        $response['sample'] = $rows;
    }

    // Also return full JSON produced by the main API for quick comparison
    $resAll = $conn->query("SELECT * FROM collections ORDER BY id DESC");
    if ($resAll) {
        $all = [];
        while ($r = $resAll->fetch_assoc()) $all[] = $r;
        $response['api_json'] = $all;
    }

    $response['ok'] = true;
    $response['message'] = 'Fetched collections info';
} catch (Exception $ex) {
    $response['error'] = $ex->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
