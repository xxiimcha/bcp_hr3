<?php
include '../config.php';
session_start();

// Ensure that the employee is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// Fetch the form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_code = $_POST['leave_type'];  // Using leave_code from the dropdown
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $remarks = $_POST['remarks'];

    // Get the current date
    $current_date = new DateTime();

    // Calculate the requested start date as DateTime object
    $start_date_obj = new DateTime($start_date);

    // Check if the requested leave is at least 7 days ahead, except for emergency or sick leave
    if ($leave_code != 'EL' && $leave_code != 'SL' && $start_date_obj < $current_date->modify('+7 days')) {
        $_SESSION['message'] = "You must request leave at least 7 days in advance, except for emergency and sick leave.";
        header("Location: leave-request.php?message=" . urlencode("Request must be 7 days in advance."));
        exit();
    }

    // Calculate total days of leave (start date to end date inclusive)
    $end_date_obj = new DateTime($end_date);
    $interval = $start_date_obj->diff($end_date_obj);
    $total_days = $interval->days + 1; // Including the start day

    // Check leave balance for the requested leave type
    $leaveBalanceSql = "SELECT lb.balance, lt.leave_id FROM employee_leave_balances lb 
                        JOIN leave_types lt ON lb.leave_code = lt.leave_code 
                        WHERE lb.employee_id = ? AND lt.leave_code = ?";
    $leaveBalanceStmt = $conn->prepare($leaveBalanceSql);
    $leaveBalanceStmt->bind_param("is", $employee_id, $leave_code);  // Binding employee_id and leave_code
    $leaveBalanceStmt->execute();
    $leaveBalanceResult = $leaveBalanceStmt->get_result();

    if ($leaveBalanceResult->num_rows > 0) {
        $leaveData = $leaveBalanceResult->fetch_assoc();
        $leaveBalance = $leaveData['balance'];
        $leave_id = $leaveData['leave_id'];

        // Check if the employee has enough leave balance
        if ($leaveBalance >= $total_days) {
            // Employee has sufficient leave balance, proceed with the leave request
            $status = 'Pending'; // Default status for new leave requests

            // Insert the leave request into the employee_leave_requests table
            $insertSql = "INSERT INTO employee_leave_requests (employee_id, leave_id, start_date, end_date, total_days, status, remarks) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iississ", $employee_id, $leave_id, $start_date, $end_date, $total_days, $status, $remarks);
            $insertStmt->execute();

            // Redirect to a confirmation page or back to the leave page with a success message
            $_SESSION['message'] = "Leave request submitted successfully!";
            header("Location: leave-request.php?message=" . urlencode("Leave request submitted successfully!"));
            exit();
        } else {
            // Employee does not have enough leave balance
            $_SESSION['message'] = "You do not have enough leave balance for this request.";
            header("Location: leave-request.php?message=" . urlencode("Insufficient leave balance."));
            exit();
        }
    } else {
        // Leave type not found for the employee
        $_SESSION['message'] = "Invalid leave type.";
        header("Location: leave-request.php?message=" . urlencode("Invalid leave type."));
        exit();
    }
}
?>
