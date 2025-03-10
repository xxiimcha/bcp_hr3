<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../log-in.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_id = $_POST['department_id']; // Assuming this is passed from the form
    $department_name = $_POST['department_name'];

    // Update the department in the departments table
    $query = "UPDATE departments SET department_name = ? WHERE department_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $department_name, $department_id);

    if ($stmt->execute()) {
        // Update the department_id in the employee_info table
        $updateEmployeeQuery = "UPDATE employee_info SET department_id = ? WHERE department_id = ?";
        $updateStmt = $conn->prepare($updateEmployeeQuery);
        $updateStmt->bind_param("ii", $department_id, $department_id);

        if ($updateStmt->execute()) {
            // Redirect back to the department page or display a success message
            header("Location: department.php?update=success");
            exit();
        } else {
            // Handle error
            echo "Error updating employee records: " . $conn->error;
        }

        $updateStmt->close();
    } else {
        // Handle error
        echo "Error updating department: " . $conn->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
