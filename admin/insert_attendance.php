<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect data from POST request
    $employee_id = $_POST['employee_id'];
    $date = date('Y-m-d');
    $time_in = date('H:i:s');
    
    // Assuming these values are also sent via POST
    $time_out = $_POST['time_out'] ?? null; // You might want to set a default or manage it
    $overtime_in = $_POST['overtime_in'] ?? null; // Handle accordingly if not applicable
    $overtime_out = $_POST['overtime_out'] ?? null; // Handle accordingly if not applicable
    $employee_shift_id = $_POST['employee_shift_id']; // Ensure this is sent in the request
    $attendance_status = 'Present'; // Default attendance status

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, time_in, time_out, overtime_in, overtime_out, employee_shift_id, attendance_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssis", $employee_id, $date, $time_in, $time_out, $overtime_in, $overtime_out, $employee_shift_id, $attendance_status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>
