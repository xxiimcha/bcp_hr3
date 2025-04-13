<?php
if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    // Use the actual IP address of the ESP8266 on your local network
    $esp_ip = "192.168.254.200"; // replace with your ESP8266's IP if different
    $ngrok_url = "http://$esp_ip/start_enroll?employee_id=" . urlencode($employee_id);

    $response = @file_get_contents($ngrok_url);

    if ($response !== false) {
        header('Content-Type: text/plain');
        echo $response;
    } else {
        http_response_code(500);
        echo "Failed to reach ESP8266 at $esp_ip.";
    }
} else {
    http_response_code(400);
    echo "Missing employee_id.";
}
