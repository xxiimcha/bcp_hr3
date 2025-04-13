<?php
require '../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$fingerprint_id = $_POST['fingerprint_id'] ?? '';

if (empty($fingerprint_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fingerprint ID']);
    exit;
}

// Log the received ID
file_put_contents('lookup_log.txt', "Looking up fingerprint_id: $fingerprint_id\n", FILE_APPEND);

$stmt = $conn->prepare("SELECT * FROM employee_fingerprints WHERE fingerprint_id = ?");
$stmt->bind_param("i", $fingerprint_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(['status' => 'matched', 'employee_id' => $data['employee_id']]);
    exit;
} else {
    echo json_encode(['status' => 'notfound', 'message' => 'Fingerprint ID not found in system']);
    exit;
}
