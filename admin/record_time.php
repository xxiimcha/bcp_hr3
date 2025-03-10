<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Get POST data
$employee_id = $_POST['employee_id'];
$time_type = $_POST['time_type'];
$timestamp = new DateTime(); // Current timestamp
$attendance_date = $timestamp->format('Y-m-d'); // Extract the date for attendance

// Determine time based on type
$time_value = $timestamp->format('H:i:s'); // Current time
$time_in = null;
$time_out = null;
$overtime_in = null;
$overtime_out = null;

// Validate inputs
if (empty($employee_id) || empty($time_type)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

// Prepare SQL statement based on time type
if ($time_type === 'time-in') {
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, attendance_date, time_in) VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE time_in = VALUES(time_in)");
    $stmt->bind_param("iss", $employee_id, $attendance_date, $time_value);
} elseif ($time_type === 'time-out') {
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, attendance_date, time_out) VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE time_out = VALUES(time_out)");
    $stmt->bind_param("iss", $employee_id, $attendance_date, $time_value);
} elseif ($time_type === 'overtime-in') {
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, attendance_date, overtime_in) VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE overtime_in = VALUES(overtime_in)");
    $stmt->bind_param("iss", $employee_id, $attendance_date, $time_value);
} elseif ($time_type === 'overtime-out') {
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, attendance_date, overtime_out) VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE overtime_out = VALUES(overtime_out)");
    $stmt->bind_param("iss", $employee_id, $attendance_date, $time_value);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid time type.']);
    exit();
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Time recorded successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record time.']);
}

$stmt->close();
$conn->close();
?>
