<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Initialize message variable
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $leave_code = $_POST['leave_code'];
    $balance = $_POST['balance'];

    // Fetch Default Credit for the selected leave type
    $default_credit_query = "SELECT DefaultCredit FROM leave_types WHERE leave_code = ?";
    $stmt = $conn->prepare($default_credit_query);
    $stmt->bind_param("s", $leave_code);
    $stmt->execute();
    $stmt->bind_result($default_credit);
    $stmt->fetch();
    $stmt->close();

    // Check if the balance is valid
    if ($balance <= $default_credit) {
        // Check if a leave balance already exists for the employee and leave type
        $check_query = "SELECT balance FROM employee_leave_balances WHERE employee_id = ? AND leave_code = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("is", $employee_id, $leave_code);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // If a record exists, update the balance
            $check_stmt->bind_result($existing_balance);
            $check_stmt->fetch();
            $new_balance = $balance; // Update logic can be customized

            // Update the balance
            $update_query = "UPDATE employee_leave_balances SET balance = ? WHERE employee_id = ? AND leave_code = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("iis", $new_balance, $employee_id, $leave_code);

            if ($update_stmt->execute()) {
                $message = "Leave balance updated successfully.";
            } else {
                $message = "Error updating leave balance: " . $update_stmt->error;
            }

            $update_stmt->close();
        } else {
            // If no record exists, insert a new one
            $insert_query = "INSERT INTO employee_leave_balances (employee_id, leave_code, balance) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("isi", $employee_id, $leave_code, $balance);

            if ($insert_stmt->execute()) {
                $message = "Leave balance added successfully.";
            } else {
                $message = "Error adding leave balance: " . $insert_stmt->error;
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    } else {
        $message = "Error: Balance exceeds Default Credit.";
    }
    $conn->close();
    header("Location: manage-leave.php?employee_id=" . urlencode($employee_id));     
    exit();
}


?>
