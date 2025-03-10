<?php
include '../config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../log-in.php");
    exit();
}

// Check if department ID is provided
if (isset($_POST['department_id'])) {
    $department_id = $_POST['department_id'];

    // Prepare the SQL delete statement
    $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
    $stmt->bind_param("i", $department_id);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: department.php?delete=success");
        exit();        
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
