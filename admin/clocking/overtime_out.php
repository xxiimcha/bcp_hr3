<?php
include '../../config.php';
session_start();

// Set the timezone to Philippines (UTC+8)
date_default_timezone_set('Asia/Manila');

if (!isset($_POST['employee_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Employee ID not provided']);
    exit();
}

$employee_id = $_POST['employee_id'];
$attendance_date = date('Y-m-d');   // Current date in Philippine timezone
$current_time = date('H:i:s');      // Current time in Philippine timezone

try {
    // Fetch the employee's shift details
    $shiftQuery = "
        SELECT st.shift_start, st.shift_end 
        FROM employee_shifts es
        JOIN shift_types st ON es.shift_type_id = st.shift_type_id
        WHERE es.employee_id = ?
        LIMIT 1
    ";
    $shiftStmt = $conn->prepare($shiftQuery);
    $shiftStmt->bind_param("i", $employee_id);
    $shiftStmt->execute();
    $shiftResult = $shiftStmt->get_result();

    if ($shiftResult->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No shift assigned to employee']);
        exit();
    }

    $shiftData = $shiftResult->fetch_assoc();
    $shift_start = $shiftData['shift_start'];
    $shift_end = $shiftData['shift_end'];

    // Check if current time is within shift hours
    if (strtotime($current_time) >= strtotime($shift_start) && strtotime($current_time) <= strtotime($shift_end)) {
        echo json_encode(['status' => 'error', 'message' => 'Overtime Out not recorded: Current time is within shift hours.']);
        exit();
    }

    // Check if an attendance record for the employee on the current date already exists
    $query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $employee_id, $attendance_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Attendance record exists, update it with the Overtime Out details
        $updateQuery = "UPDATE attendance SET overtime_out = ?, status = 'Overtime Out' WHERE employee_id = ? AND attendance_date = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sis", $current_time, $employee_id, $attendance_date);
        
        if ($updateStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Overtime Out recorded successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error recording Overtime Out']);
        }
    } else {
        // No attendance record exists; insert a new one
        $insertQuery = "INSERT INTO attendance (employee_id, attendance_date, overtime_out, status) VALUES (?, ?, ?, 'Overtime Out')";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iss", $employee_id, $attendance_date, $current_time);
        
        if ($insertStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Overtime Out recorded successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error recording Overtime Out']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
