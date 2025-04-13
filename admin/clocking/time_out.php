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
$current_date = date('Y-m-d');   // Current date in Philippine timezone
$current_time = date('H:i:s');   // Current time in Philippine timezone
$overtimeThresholdHours = 1;     // Set the desired number of hours for overtime threshold

try {
    // Check the employee's shift details, including shift end time
    $shiftQuery = "
        SELECT st.shift_start, st.shift_end 
        FROM emp_shifts es
        JOIN shift_types st ON es.shift_type_id = st.shift_type_id
        WHERE es.employee_id = ?
        LIMIT 1
    ";
    $shiftStmt = $conn->prepare($shiftQuery);
    $shiftStmt->bind_param("i", $employee_id);
    $shiftStmt->execute();
    $shiftResult = $shiftStmt->get_result();

    if ($shiftResult->num_rows > 0) {
        $shiftData = $shiftResult->fetch_assoc();
        $shift_start = $shiftData['shift_start'];
        $shift_end = $shiftData['shift_end'];

        // Calculate shift start and shift end as DateTime objects
        $attendance_date = $current_date;
        $shift_start_datetime = new DateTime("$attendance_date $shift_start");
        $shift_end_datetime = new DateTime("$attendance_date $shift_end");

        // If the shift ends after midnight, adjust the shift end to the next day
        if ($shift_end < $shift_start) {
            $shift_end_datetime->modify('+1 day');
        }

        // Check for an attendance record for the employee from either today or yesterday
        $query = "
            SELECT * FROM attendance 
            WHERE employee_id = ? 
              AND (attendance_date = ? OR attendance_date = ?)
            ORDER BY attendance_date DESC 
            LIMIT 1
        ";
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $employee_id, $current_date, $yesterday);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // No attendance record found for today or yesterday; no time-in
            echo json_encode(['status' => 'error', 'message' => 'No time-in record found for today or yesterday']);
        } else {
            // Attendance record exists, proceed with time-out
            $attendanceData = $result->fetch_assoc();
            $attendance_date = $attendanceData['attendance_date'];  // Use the correct attendance date
            $timeDifference = strtotime($current_time) - strtotime($shift_end_datetime->format('H:i:s'));

            if ($timeDifference < 0) {
                $status = 'Undertime';
            } elseif ($timeDifference > ($overtimeThresholdHours * 3600)) {
                $status = 'Overtime';
            } else {
                $status = 'Present';
            }

            // Update the attendance record with time_out and status
            $updateQuery = "UPDATE attendance SET time_out = ?, status = ? WHERE employee_id = ? AND attendance_date = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssis", $current_time, $status, $employee_id, $attendance_date);
            
            if ($updateStmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => "Time out recorded successfully. Status: $status"]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error recording time out']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Shift details not found for the employee']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
