<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_code = $_POST['leave_code'];
    $leave_type = $_POST['leave_type'];
    $default_credit = $_POST['DefaultCredit'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE leave_types SET leave_type = ?, DefaultCredit = ? WHERE leave_code = ?");
    $stmt->bind_param("sis", $leave_type, $default_credit, $leave_code);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Leave Type updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating Leave Type: " . $conn->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to the list page
    header("Location: leave-type-list.php"); // Replace with your actual page
    exit();
}
?>
