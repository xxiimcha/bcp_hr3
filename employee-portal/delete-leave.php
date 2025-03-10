<?php
include '../config.php';  // Include the database connection configuration
session_start();

// Ensure that the employee is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php");  // Redirect to login if not logged in
    exit();
}

$employee_id = $_SESSION['employee_id'];  // Get the employee ID from the session

// Check if start_date is provided in the POST request
if (isset($_POST['start_date'])) {
    // Get the start_date from the POST request
    $start_date = $_POST['start_date'];

    // Prepare the SQL query to delete the leave request for the specific employee and start date
    $deleteLeaveSql = "DELETE FROM employee_leave_requests WHERE employee_id = ? AND start_date = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($deleteLeaveSql)) {
        // Bind parameters: employee_id and start_date (the employee ID is integer and start date is string/date)
        $stmt->bind_param("is", $employee_id, $start_date);

        // Execute the query
        if ($stmt->execute()) {
            // If successful, redirect to the leave requests page
            header('Location: leave-request.php');  // Change to your actual page for leave requests
            exit();
        } else {
            // If there was an error during deletion, show error message
            echo "Error deleting leave request: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // If the statement couldn't be prepared, show error message
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    // If no start_date is provided, show error message
    echo "Invalid request. No start date provided.";
}
?>
