<?php
include '../config.php';

// Check if the employee IDs were sent
if (isset($_POST['employee_ids'])) {
    $employee_ids = json_decode($_POST['employee_ids'], true);

    if (!empty($employee_ids)) {
        // Prepare the SQL statement to delete the selected shifts
        $placeholders = implode(',', array_fill(0, count($employee_ids), '?'));
        $stmt = $conn->prepare("DELETE FROM emp_shifts WHERE employee_id IN ($placeholders)");

        // Bind the parameters dynamically
        $types = str_repeat('i', count($employee_ids)); // Assuming employee_id is an integer
        $stmt->bind_param($types, ...$employee_ids);

        if ($stmt->execute()) {
            echo "Shifts deleted successfully.";
        } else {
            echo "Failed to delete shifts. Please try again.";
        }

        $stmt->close();
    } else {
        echo "No shifts selected for deletion.";
    }
} else {
    echo "No employee IDs received.";
}

// Close the database connection
$conn->close();
?>
