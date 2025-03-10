<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendance_id = $_POST['attendance_id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    // Validate input
    if ($field != 'time_in' && $field != 'time_out') {
        echo json_encode(['error' => 'Invalid field']);
        exit();
    }

    // Prepare SQL to update the field
    $update_sql = "UPDATE attendance SET $field = ? WHERE attendance_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $value, $attendance_id);
    $stmt->execute();

    // If the field is 'time_in', we update the status to 'Present'
    if ($field == 'time_in') {
        $update_status_sql = "UPDATE attendance SET status = 'Present' WHERE attendance_id = ?";
        $stmt = $conn->prepare($update_status_sql);
        $stmt->bind_param('i', $attendance_id);
        $stmt->execute();
    }

    // Now fetch the updated row for recalculation
    $sql = "SELECT a.attendance_id, a.employee_id, ei.employee_name, d.department_name, ei.position, 
            a.attendance_date, a.status, a.time_in, a.time_out, a.overtime_in, a.overtime_out,
            st.shift_start, st.shift_end
            FROM attendance a
            JOIN employee_info ei ON a.employee_id = ei.employee_id
            JOIN departments d ON ei.department_id = d.department_id
            LEFT JOIN employee_shifts es ON a.employee_id = es.employee_id
            LEFT JOIN shift_types st ON es.shift_type_id = st.shift_type_id
            WHERE a.attendance_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $attendance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Initialize calculation variables
    $worked_hours = 0;
    $early_arrival_hours = 0;
    $late_departure_hours = 0;
    $total_overtime_hours = 0;
    $status = $row['status']; // Default status

    if (!is_null($row['time_in']) && !is_null($row['time_out'])) {
        $time_in = new DateTime($row['time_in']);
        $time_out = new DateTime($row['time_out']);
        $shift_start = new DateTime($row['shift_start']);
        $shift_end = new DateTime($row['shift_end']);

        // Calculate worked hours strictly within shift times
        $actual_start_time = max($time_in, $shift_start);
        $actual_end_time = min($time_out, $shift_end);

        if ($actual_start_time < $actual_end_time) {
            $worked_hours = $actual_start_time->diff($actual_end_time)->h + ($actual_start_time->diff($actual_end_time)->i / 60);
        }

        // Early Arrival (Overtime before shift start)
        if ($time_in < $shift_start) {
            $early_arrival_hours = $shift_start->diff($time_in)->h + ($shift_start->diff($time_in)->i / 60);
        }

        // Late Departure (Overtime after shift end)
        if ($time_out > $shift_end) {
            $late_departure_hours = $time_out->diff($shift_end)->h + ($time_out->diff($shift_end)->i / 60);
        }

        // Calculate total overtime hours (excluding it from worked hours)
        $total_overtime_hours = $early_arrival_hours + $late_departure_hours;

        // Update the status based on time_in and time_out
        if ($time_in > $shift_start) {
            $status = 'Late'; // If time_in exceeds shift_start, status is 'Late'
        }
        if ($time_out < $shift_end) {
            $status = 'Undertime'; // If time_out is earlier than shift_end, status is 'Undertime'
        }
    }

    // Update the status in the database if it has changed
    if ($status != $row['status']) {
        $update_status_sql = "UPDATE attendance SET status = ? WHERE attendance_id = ?";
        $stmt = $conn->prepare($update_status_sql);
        $stmt->bind_param('si', $status, $attendance_id);
        $stmt->execute();
    }

    // Prepare the data to return as JSON
    $data = [
        'time_in' => $row['time_in'],
        'time_out' => $row['time_out'],
        'worked_hours' => number_format($worked_hours, 2),
        'total_overtime_hours' => number_format($total_overtime_hours, 2),
        'status' => $status // Include the updated status
    ];

    echo json_encode($data);
}

$conn->close();
?>
