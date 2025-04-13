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
$overtimeThresholdHours = 1;        // Set the desired number of hours for overtime threshold

try {
    // Fetch the employee's shift start time
    $shiftQuery = "
        SELECT st.shift_start 
        FROM emp_shifts es
        JOIN shift_types st ON es.shift_type_id = st.shift_type_id
        WHERE es.employee_id = ?
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
    $shift_start_time = $shiftData['shift_start'];

    // Calculate overtime threshold in seconds
    $overtimeThresholdSeconds = $overtimeThresholdHours * 3600;

    // Determine attendance status
    $timeDifference = strtotime($shift_start_time) - strtotime($current_time);

    if ($timeDifference >= $overtimeThresholdSeconds) {
        $status = 'Overtime';
    } elseif (strtotime($current_time) <= strtotime($shift_start_time)) {
        $status = 'Present';
    } else {
        $status = 'Late';
    }

    // Check if an attendance record for the employee on the current date already exists
    $query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $employee_id, $attendance_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Attendance record exists, possibly update it or return a message
        echo json_encode(['status' => 'error', 'message' => 'Attendance record already exists for today']);
    } else {
        // Insert new attendance record
        $insertQuery = "INSERT INTO attendance (employee_id, attendance_date, time_in, status) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("isss", $employee_id, $attendance_date, $current_time, $status);
        if ($insertStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "Time in recorded successfully. Status: $status"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error recording time in']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
