<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

include '../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$employee_id = $_POST['employee_id'] ?? '';
$shift_id = $_POST['shift_id'] ?? null; // Optional: can be handled if shift types are implemented

if (empty($employee_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing employee ID']);
    exit;
}

$employee_id = mysqli_real_escape_string($conn, $employee_id);
$date_today = date('Y-m-d');
$datetime_now = date('Y-m-d H:i:s');

// Check if a timesheet entry already exists for today with time_in but no time_out
$check = "SELECT * FROM employee_timesheet WHERE employee_id = '$employee_id' AND DATE(time_in) = '$date_today'";
$result = mysqli_query($conn, $check);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    if (empty($row['time_out']) || $row['time_out'] === '0000-00-00 00:00:00') {
        // Update time_out
        $update = "UPDATE employee_timesheet SET time_out = '$datetime_now' WHERE timesheet_id = '{$row['timesheet_id']}'";
        if (mysqli_query($conn, $update)) {
            echo json_encode(['status' => 'success', 'action' => 'timeout', 'message' => 'Time out recorded']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update time_out']);
        }
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Already timed in and out today']);
    }
} else {
    // Insert time_in
    $insert = "INSERT INTO employee_timesheet (employee_id, shift_id, time_in) VALUES ('$employee_id', '$shift_id', '$datetime_now')";
    if (mysqli_query($conn, $insert)) {
        echo json_encode(['status' => 'success', 'action' => 'timein', 'message' => 'Time in recorded']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert time_in']);
    }
}

mysqli_close($conn);
