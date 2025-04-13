<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $leave_id = (int) $_POST['leave_code'];  // should be numeric leave_id
    $balance = (int) $_POST['balance'];

    // 1. Get default credit
    $default_sql = "SELECT DefaultCredit FROM leave_types WHERE leave_id = $leave_id";
    $default_result = mysqli_query($conn, $default_sql);

    if (!$default_result || mysqli_num_rows($default_result) == 0) {
        die("Error fetching Default Credit.");
    }

    $default_data = mysqli_fetch_assoc($default_result);
    $default_credit = $default_data['DefaultCredit'];

    if ($balance <= $default_credit) {
        // 2. Check if leave balance record exists
        $check_sql = "SELECT balance FROM employee_leave_balances WHERE employee_id = '$employee_id' AND leave_id = $leave_id";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            // 3. Update existing
            $update_sql = "UPDATE employee_leave_balances SET balance = $balance WHERE employee_id = '$employee_id' AND leave_id = $leave_id";
            if (mysqli_query($conn, $update_sql)) {
                $message = "Leave balance updated successfully.";
            } else {
                $message = "Error updating leave balance: " . mysqli_error($conn);
            }
        } else {
            // 4. Insert new
            $insert_sql = "INSERT INTO employee_leave_balances (employee_id, leave_id, balance) VALUES ('$employee_id', $leave_id, $balance)";
            if (mysqli_query($conn, $insert_sql)) {
                $message = "Leave balance added successfully.";
            } else {
                $message = "Error adding leave balance: " . mysqli_error($conn);
            }
        }
    } else {
        $message = "Error: Balance exceeds Default Credit.";
    }

    mysqli_close($conn);

    // For AJAX or fallback
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo $message;
    } else {
        header("Location: manage-leave.php?employee_id=" . urlencode($employee_id));
        exit();
    }
}
?>
