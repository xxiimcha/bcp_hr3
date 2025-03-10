<?php
// Include your database connection file
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the record ID from the POST request
    $record_id = intval($_POST['record_id']);
    // Get the employee ID from the POST request or session (make sure to include this in your form)
    $employee_id = intval($_POST['employee_id']);

    // Prepare the SQL DELETE statement
    $sql = "DELETE FROM employee_leave_records WHERE record_id = ?";

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $record_id); // Bind the record_id parameter
        if ($stmt->execute()) {
            // Record deleted successfully, redirecting to manage-leave.php
            header("Location: manage-leave.php?employee_id=" . $employee_id . "&success=Record deleted successfully.");
            exit;
        } else {
            // Error deleting record, redirecting with error message
            header("Location: manage-leave.php?employee_id=" . $employee_id . "&error=Could not delete record.");
            exit;
        }
    } else {
        // Error preparing statement, redirecting with error message
        header("Location: manage-leave.php?employee_id=" . $employee_id . "&error=Database error.");
        exit;
    }

    $stmt->close();
}
$conn->close();
?>
