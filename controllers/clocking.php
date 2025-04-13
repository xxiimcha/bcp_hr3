<?php
include '../config.php';
date_default_timezone_set('Asia/Manila'); // Make sure all time is GMT+8
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'lookup':
        lookupByFingerprint($conn);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function lookupByFingerprint($conn) {
    date_default_timezone_set('Asia/Manila');
    $fingerprint_id = isset($_GET['fingerprint_id']) ? intval($_GET['fingerprint_id']) : 0;

    if (!$fingerprint_id) {
        echo json_encode(['status' => 'error', 'message' => 'Missing fingerprint ID']);
        return;
    }

    // 1. Get employee_id
    $res = mysqli_query($conn, "SELECT employee_id FROM employee_fingerprints WHERE fingerprint_id = '$fingerprint_id' LIMIT 1");
    if (mysqli_num_rows($res) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Fingerprint not found']);
        return;
    }

    $row = mysqli_fetch_assoc($res);
    $employee_id = $row['employee_id'];

    // 2. Check for open time-in
    $openRes = mysqli_query($conn, "SELECT * FROM employee_timesheet WHERE employee_id = '$employee_id' AND time_out = '0000-00-00 00:00:00' ORDER BY timesheet_id DESC LIMIT 1");
    if (mysqli_num_rows($openRes) > 0) {
        $entry = mysqli_fetch_assoc($openRes);
        $time_in = strtotime($entry['time_in']);
        $time_out = time();
        $time_out_str = date('Y-m-d H:i:s', $time_out);
        $hours_worked = round(($time_out - $time_in) / 3600, 2);

        // 3. Get shift end
        $shiftInfo = mysqli_query($conn, "
            SELECT s.shift_end 
            FROM emp_shifts e
            JOIN shift_types s ON e.shift_type_id = s.shift_type_id
            WHERE e.employee_id = '$employee_id' LIMIT 1
        ");
        $shift = mysqli_fetch_assoc($shiftInfo);
        $shift_end_str = $shift['shift_end'];
        $shift_end_ts = strtotime(date('Y-m-d') . ' ' . $shift_end_str);

        // Handle overnight shift
        if ($shift_end_ts < $time_in) {
            $shift_end_ts = strtotime(date('Y-m-d', strtotime('+1 day')) . ' ' . $shift_end_str);
        }

        $overtime = 0;
        $status = 'Present';

        if ($time_out > $shift_end_ts) {
            $overtime = round(($time_out - $shift_end_ts) / 3600, 2);
        } else if ($time_out < $shift_end_ts) {
            $status = 'Undertime';
        }

        // Check for previous late flag to combine status
        if ($entry['late'] > 0 && $status == 'Undertime') {
            $status = 'Late & Undertime';
        } else if ($entry['late'] > 0) {
            $status = 'Late';
        }

        $update = "
            UPDATE employee_timesheet
            SET time_out = '$time_out_str',
                hours_worked = '$hours_worked',
                overtime_hours = '$overtime',
                status = '$status'
            WHERE timesheet_id = '{$entry['timesheet_id']}'
        ";

        if (mysqli_query($conn, $update)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Time out recorded',
                'employee_id' => $employee_id,
                'time_out' => $time_out_str,
                'hours_worked' => $hours_worked,
                'overtime_hours' => $overtime,
                'final_status' => $status
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update time out']);
        }

        return;
    }

    // 4. Clock-in handling
    $shift_sql = "
        SELECT s.shift_name, s.shift_start, s.shift_end, s.shift_type_id
        FROM emp_shifts e
        JOIN shift_types s ON e.shift_type_id = s.shift_type_id
        WHERE e.employee_id = '$employee_id'
        LIMIT 1";
    $shift_res = mysqli_query($conn, $shift_sql);
    $shift = mysqli_fetch_assoc($shift_res);

    if (!$shift) {
        echo json_encode(['status' => 'error', 'message' => 'No shift assigned']);
        return;
    }

    $shift_id = $shift['shift_type_id'];
    $shift_start = $shift['shift_start'];
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $current_datetime = $now->format('Y-m-d H:i:s');
    $current_date = $now->format('Y-m-d');
    $late_minutes = max(0, floor((strtotime($current_datetime) - strtotime("$current_date $shift_start")) / 60));

    $status = ($late_minutes > 0) ? 'Late' : 'Present';

    $insert = "
        INSERT INTO employee_timesheet (
            shift_id, employee_id, time_in, late, status
        ) VALUES (
            '$shift_id', '$employee_id', '$current_datetime', '$late_minutes', '$status'
        )";

    if (mysqli_query($conn, $insert)) {
        echo json_encode([
            'status' => 'success',
            'employee_id' => $employee_id,
            'time_in' => $current_datetime,
            'late' => $late_minutes . ' minute(s)',
            'message' => 'Time in recorded',
            'status_flag' => $status
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert time in']);
    }
}
