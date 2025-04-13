<?php
include '../config.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['shift_id'])) {
    $shiftId = $_GET['shift_id'];
    
    // Prepare and execute the delete query
    $sql = "DELETE FROM emp_shifts WHERE employee_shift_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shiftId);
    
    if ($stmt->execute()) {
        echo "Shift deleted successfully.";
    } else {
        echo "Error deleting shift.";
    }
    
    $stmt->close();
} else {
    echo "No shift ID provided.";
}

$conn->close();
?>
