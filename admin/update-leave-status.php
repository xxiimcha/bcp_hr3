<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';

// Sanitize input
$employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
$leave_id = mysqli_real_escape_string($conn, $_POST['leave_id']);
$total_days = mysqli_real_escape_string($conn, $_POST['total_days']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
$end_date = mysqli_real_escape_string($conn, $_POST['end_date']);

// Get current status
$currentStatusSql = "SELECT status FROM employee_leave_requests WHERE employee_id = '$employee_id' AND leave_id = '$leave_id'";
$currentStatusResult = mysqli_query($conn, $currentStatusSql);
$currentStatusRow = mysqli_fetch_assoc($currentStatusResult);
$current_status = $currentStatusRow['status'] ?? null;

// Delete if already approved
if ($current_status === 'Approved') {
    mysqli_query($conn, "DELETE FROM employee_leave_requests WHERE employee_id = '$employee_id' AND leave_id = '$leave_id'");
    echo "<script>alert('Leave request was already approved and has been deleted.'); window.location.href='leavemanagement.php';</script>";
    exit();
}

// Fetch remarks
$remarksSql = "SELECT remarks FROM employee_leave_requests WHERE employee_id = '$employee_id' AND leave_id = '$leave_id'";
$remarksResult = mysqli_query($conn, $remarksSql);
$remarksRow = mysqli_fetch_assoc($remarksResult);
$remarks = mysqli_real_escape_string($conn, $remarksRow['remarks'] ?? '');

// If approving, check balance
if ($status === 'Approved') {
    $leaveCodeSql = "SELECT lt.leave_code FROM employee_leave_requests elr
                     JOIN leave_types lt ON elr.leave_id = lt.leave_id
                     WHERE elr.employee_id = '$employee_id' AND elr.leave_id = '$leave_id'";
    $leaveCodeResult = mysqli_query($conn, $leaveCodeSql);
    $leaveCodeRow = mysqli_fetch_assoc($leaveCodeResult);
    $leave_code = mysqli_real_escape_string($conn, $leaveCodeRow['leave_code'] ?? '');

    $balanceSql = "SELECT balance FROM employee_leave_balances WHERE employee_id = '$employee_id' AND leave_code = '$leave_code'";
    $balanceResult = mysqli_query($conn, $balanceSql);
    $balanceRow = mysqli_fetch_assoc($balanceResult);
    $current_balance = (int)($balanceRow['balance'] ?? 0);

    if ($current_balance < $total_days) {
        echo "<script>alert('Insufficient leave balance. Leave request status remains pending.'); window.location.href='leavemanagement.php';</script>";
        exit();
    }
}

// Update status
$updateSql = "UPDATE employee_leave_requests SET status = '$status' WHERE employee_id = '$employee_id' AND leave_id = '$leave_id'";
mysqli_query($conn, $updateSql);

if ($status === 'Approved' || $status === 'Rejected') {
    $insertSql = "INSERT INTO employee_leave_records (
                    employee_id, leave_id, start_date, end_date, total_days, approval_date, status, remarks
                 ) VALUES (
                    '$employee_id', '$leave_id', '$start_date', '$end_date', '$total_days', NOW(), '$status', '$remarks'
                 )";
    $insertResult = mysqli_query($conn, $insertSql);

    if ($insertResult) {
        // Delete original leave request
        mysqli_query($conn, "DELETE FROM employee_leave_requests WHERE employee_id = '$employee_id' AND leave_id = '$leave_id'");

        // If approved, deduct balance and update attendance
        if ($status === 'Approved') {
            $deductSql = "UPDATE employee_leave_balances SET balance = balance - $total_days WHERE employee_id = '$employee_id' AND leave_code = '$leave_code'";
            mysqli_query($conn, $deductSql);

            $currentDate = strtotime($start_date);
            $endDate = strtotime($end_date);
            while ($currentDate <= $endDate) {
                $attendanceDate = date('Y-m-d', $currentDate);
                $attendanceSql = "INSERT INTO attendance (employee_id, attendance_date, status) VALUES ('$employee_id', '$attendanceDate', 'Leave')";
                mysqli_query($conn, $attendanceSql);
                $currentDate = strtotime("+1 day", $currentDate);
            }

            echo "<script>alert('Leave request approved successfully, recorded, and original request deleted.'); window.location.href='leavemanagement.php';</script>";
        } else {
            echo "<script>alert('Leave request rejected successfully and recorded.'); window.location.href='leavemanagement.php';</script>";
        }
    } else {
        echo "<script>alert('Failed to record $status leave request.'); window.location.href='leavemanagement.php';</script>";
    }
}

mysqli_close($conn);
?>
