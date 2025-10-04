<?php
// Simple proxy to forward approve (PUT) requests to external collections API
header("Content-Type: application/json; charset=utf-8");
// Read JSON body
$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!$data || empty($data['invoice_no'])) {
    echo json_encode(["success"=>false, "error"=>"Missing invoice_no"]);
    exit;
}

$external = 'http://localhost/prefect/api/collections.php';

$ch = curl_init($external);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($body)
]);

$resp = curl_exec($ch);
$err = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    echo json_encode(["success"=>false, "error"=>"Proxy error: $err"]);
    exit;
}

// Try to forward JSON response; if not JSON, wrap it
if ($resp) {
    $decoded = json_decode($resp, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo $resp;
    } else {
        // Return as error text to client
        echo json_encode(["success"=>false, "error"=>"External API returned non-JSON response", "raw"=>substr($resp,0,1000)]);
    }
} else {
    echo json_encode(["success"=>false, "error"=>"Empty response from external API", "http_status"=>$status]);
}

exit;
