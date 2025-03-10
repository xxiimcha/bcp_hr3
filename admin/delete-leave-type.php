<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['leave_code'])) {
    $leave_code = $_GET['leave_code'];

    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM leave_types WHERE leave_code = ?");
    $stmt->bind_param("s", $leave_code);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Leave type deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting leave type: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid leave code.";
}

$conn->close();

// Redirect back to the leave type list
header("Location: leave-type-list.php");
exit();
?>
