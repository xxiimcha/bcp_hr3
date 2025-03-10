<?php
// Include the database connection
include '../config.php';

// Retrieve form data
$employee_id = $_POST['employee_id'];
$leave_id = $_POST['leave_id'];
$total_days = $_POST['total_days'];
$status = $_POST['status'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// Check the current status of the leave request
$currentStatusQuery = "SELECT status FROM employee_leave_requests WHERE employee_id = ? AND leave_id = ?";
$currentStatusStmt = $conn->prepare($currentStatusQuery);
$currentStatusStmt->bind_param("ii", $employee_id, $leave_id);
$currentStatusStmt->execute();
$currentStatusStmt->bind_result($current_status);
$currentStatusStmt->fetch();
$currentStatusStmt->close();

// If the current status is already 'Approved', delete the leave request
if ($current_status === 'Approved') {
    $deleteRequestQuery = "DELETE FROM employee_leave_requests WHERE employee_id = ? AND leave_id = ?";
    $deleteRequestStmt = $conn->prepare($deleteRequestQuery);
    $deleteRequestStmt->bind_param("ii", $employee_id, $leave_id);
    $deleteRequestStmt->execute();
    $deleteRequestStmt->close();

    // Success message
    echo "<script>alert('Leave request was already approved and has been deleted.'); window.location.href='leavemanagement.php';</script>";
    exit();
}

// Prepare to check current leave balance only if the status is 'Approved'
if ($status === 'Approved') {
    // Get the leave_code and remarks from employee_leave_requests table
    $leaveCodeQuery = "SELECT lt.leave_code, elr.remarks FROM employee_leave_requests elr JOIN leave_types lt ON elr.leave_id = lt.leave_id WHERE elr.employee_id = ? AND elr.leave_id = ?";
    $leaveCodeStmt = $conn->prepare($leaveCodeQuery);
    $leaveCodeStmt->bind_param("ii", $employee_id, $leave_id);
    $leaveCodeStmt->execute();
    $leaveCodeStmt->bind_result($leave_code, $remarks);
    $leaveCodeStmt->fetch();
    $leaveCodeStmt->close();

    // Check current leave balance
    $balanceQuery = "SELECT balance FROM employee_leave_balances WHERE employee_id = ? AND leave_code = ?";
    $balanceStmt = $conn->prepare($balanceQuery);
    $balanceStmt->bind_param("is", $employee_id, $leave_code);
    $balanceStmt->execute();
    $balanceStmt->bind_result($current_balance);
    $balanceStmt->fetch();
    $balanceStmt->close();

    // Check if the balance is sufficient
    if ($current_balance < $total_days) {
        // Insufficient balance error message
        echo "<script>alert('Insufficient leave balance. Leave request status remains pending.'); window.location.href='leavemanagement.php';</script>";
        exit();
    }
}

// Update the status in employee_leave_requests table if not already approved
$updateRequestQuery = "UPDATE employee_leave_requests SET status = ? WHERE employee_id = ? AND leave_id = ?";
$stmt = $conn->prepare($updateRequestQuery);
$stmt->bind_param("sii", $status, $employee_id, $leave_id);
$stmt->execute();

// If the status is approved, proceed with further operations
if ($status === 'Approved') {
    // Insert into employee_leave_records table including remarks
    $insertRecordQuery = "INSERT INTO employee_leave_records (employee_id, leave_id, start_date, end_date, total_days, approval_date, status, remarks) VALUES (?, ?, ?, ?, ?, NOW(), 'Approved', ?)";
    $insertRecordStmt = $conn->prepare($insertRecordQuery);
    $insertRecordStmt->bind_param("iissis", $employee_id, $leave_id, $start_date, $end_date, $total_days, $remarks);
    $insertRecordStmt->execute();

    // Check if the insert was successful
    if ($insertRecordStmt->affected_rows > 0) {
        // Delete the leave request after inserting to employee_leave_records
        $deleteRequestQuery = "DELETE FROM employee_leave_requests WHERE employee_id = ? AND leave_id = ?";
        $deleteRequestStmt = $conn->prepare($deleteRequestQuery);
        $deleteRequestStmt->bind_param("ii", $employee_id, $leave_id);
        $deleteRequestStmt->execute();
        $deleteRequestStmt->close();

        // Deduct leave balance
        $deductBalanceQuery = "UPDATE employee_leave_balances SET balance = balance - ? WHERE employee_id = ? AND leave_code = ?";
        $deductBalanceStmt = $conn->prepare($deductBalanceQuery);
        $deductBalanceStmt->bind_param("iis", $total_days, $employee_id, $leave_code);
        $deductBalanceStmt->execute();

        // Insert records into the attendance table for the leave duration
        $currentDate = strtotime($start_date);
        $endDate = strtotime($end_date);
        while ($currentDate <= $endDate) {
            $attendanceDate = date('Y-m-d', $currentDate);
            $insertAttendanceQuery = "INSERT INTO attendance (employee_id, attendance_date, status) VALUES (?, ?, 'Leave')";
            $insertAttendanceStmt = $conn->prepare($insertAttendanceQuery);
            $insertAttendanceStmt->bind_param("is", $employee_id, $attendanceDate);
            $insertAttendanceStmt->execute();
            $insertAttendanceStmt->close();

            // Increment the current date by one day
            $currentDate = strtotime("+1 day", $currentDate);
        }

        // Close statements
        $insertRecordStmt->close();
        $deductBalanceStmt->close();

        // Success message
        echo "<script>alert('Leave request approved successfully, recorded, and original request deleted.'); window.location.href='leavemanagement.php';</script>";
        exit();
    } else {
        // Insertion error message
        echo "<script>alert('Failed to record leave request.'); window.location.href='leavemanagement.php';</script>";
        exit();
    }
} elseif ($status === 'Rejected') {
    // Get remarks for the rejected leave request
    $remarksQuery = "SELECT remarks FROM employee_leave_requests WHERE employee_id = ? AND leave_id = ?";
    $remarksStmt = $conn->prepare($remarksQuery);
    $remarksStmt->bind_param("ii", $employee_id, $leave_id);
    $remarksStmt->execute();
    $remarksStmt->bind_result($remarks);
    $remarksStmt->fetch();
    $remarksStmt->close();

    // Insert into employee_leave_records table for rejected request without deducting balance
    $insertRecordQuery = "INSERT INTO employee_leave_records (employee_id, leave_id, start_date, end_date, total_days, approval_date, status, remarks) VALUES (?, ?, ?, ?, ?, NOW(), 'Rejected', ?)";
    $insertRecordStmt = $conn->prepare($insertRecordQuery);
    $insertRecordStmt->bind_param("iissis", $employee_id, $leave_id, $start_date, $end_date, $total_days, $remarks);
    $insertRecordStmt->execute();

    // Check if the insert was successful
    if ($insertRecordStmt->affected_rows > 0) {
        // Delete the leave request after inserting to employee_leave_records
        $deleteRequestQuery = "DELETE FROM employee_leave_requests WHERE employee_id = ? AND leave_id = ?";
        $deleteRequestStmt = $conn->prepare($deleteRequestQuery);
        $deleteRequestStmt->bind_param("ii", $employee_id, $leave_id);
        $deleteRequestStmt->execute();
        $deleteRequestStmt->close();

        // Success message
        echo "<script>alert('Leave request rejected successfully and recorded.'); window.location.href='leavemanagement.php';</script>";
        exit();
    } else {
        // Insertion error message
        echo "<script>alert('Failed to record rejected leave request.'); window.location.href='leavemanagement.php';</script>";
        exit();
    }
}

// Close the database connection
$stmt->close();
$conn->close();
?>
