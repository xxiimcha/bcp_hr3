<?php
include '../config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $leave_type_id = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $remarks = $_POST['remarks'];
    $status = $_POST['status'];

    // Fetch the leave type from the database to check if it's emergency or sick leave
    $leaveTypeResult = $conn->query("SELECT leave_type FROM leave_types WHERE leave_id = $leave_type_id");
    $leaveTypeRow = $leaveTypeResult->fetch_assoc();
    $leave_type = $leaveTypeRow['leave_type'];

    // Get today's date and the date 7 days from now
    $today = new DateTime();
    $sevenDaysAhead = new DateTime();
    $sevenDaysAhead->modify('+7 days');

    // Convert the start date to a DateTime object
    $startDateObj = new DateTime($start_date);

    // Check if the leave type is not emergency or sick leave and if the start date is less than 7 days from today
    if ($leave_type !== 'Emergency Leave' && $leave_type !== 'Sick Leave') {
        if ($startDateObj < $sevenDaysAhead) {
            // Redirect back with an error message
            $_SESSION['error'] = 'Leave requests must be submitted at least 7 days in advance, except for emergency and sick leave.';
            header("Location: leavemanagement.php");
            exit();
        }
    }

    // Proceed with inserting the leave request into the database
    $query = "INSERT INTO employee_leave_requests (employee_id, leave_id, start_date, end_date, total_days, remarks, status) 
              VALUES ('$employee_id', '$leave_type_id', '$start_date', '$end_date', DATEDIFF('$end_date', '$start_date') + 1, '$remarks', '$status')";

    if ($conn->query($query) === TRUE) {
        $_SESSION['success'] = 'Leave request submitted successfully.';
    } else {
        $_SESSION['error'] = 'Error submitting leave request: ' . $conn->error;
    }

    $conn->close();
    header("Location: leavemanagement.php");
    exit();
}
?>
