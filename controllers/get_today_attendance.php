<?php
include '../config.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

$employee_id = $_GET['employee_id'] ?? '';

if (!$employee_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing employee_id']);
    exit;
}

$today = date('Y-m-d');

$sql = "
    SELECT * FROM employee_timesheet 
    WHERE employee_id = '$employee_id' 
    AND DATE(time_in) = '$today'
    ORDER BY timesheet_id DESC 
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Database query failed']);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'success', 'data' => null]);
}
