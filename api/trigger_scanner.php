<?php
include '../config.php';
header('Content-Type: application/json');

// ESP8266/NodeMCU endpoint
$url = "http://192.168.254.200/connect_fingerprint";

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // prevent hanging
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ignore SSL for ngrok
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode([
        "status" => "error",
        "message" => "Curl error: " . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// If non-200 status, treat as error
if ($http_code !== 200) {
    echo json_encode([
        "status" => "error",
        "message" => "Scanner HTTP error: $http_code",
        "raw_response" => substr($response, 0, 100)
    ]);
    exit;
}

// Validate JSON
$data = json_decode(trim($response), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON response from scanner: " . json_last_error_msg(),
        "raw_response" => substr($response, 0, 100) // debug help
    ]);
    exit;
}

// Process valid JSON
if (isset($data['status']) && $data['status'] === "matched" && !empty($data['employee_id'])) {
    echo json_encode([
        "status" => "matched",
        "employee_id" => $data['employee_id']
    ]);
} else {
    echo json_encode([
        "status" => "nomatch",
        "message" => $data['message'] ?? 'Fingerprint not linked to employee'
    ]);
}
