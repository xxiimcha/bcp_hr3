<?php
// Include necessary files
include '../config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch the employee ID
$employee_id = $_SESSION['employee_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form input values
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";
    } elseif (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/\d/', $new_password) || !preg_match('/[\W_]/', $new_password) || strlen($new_password) < 8) {
        $message = "New password must be at least 8 characters long, and contain one number, one letter, and one special character.";
    } else {
        // Fetch the current password hash from the database
        $sql = "SELECT password FROM employee_logins WHERE employee_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Check if the current password matches the stored password
            $row = $result->fetch_assoc();
            if (password_verify($current_password, $row['password'])) {
                // Password matches, so proceed with updating the password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $updateSql = "UPDATE employee_logins SET password = ? WHERE employee_id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $new_password_hash, $employee_id);
                if ($updateStmt->execute()) {
                    // Redirect to the portal with a success message
                    header("Location: profile.php?message=" . urlencode("Password successfully changed."));
                    exit();
                } else {
                    $message = "Failed to change password. Please try again.";
                }
            } else {
                $message = "Current password is incorrect.";
            }
        } else {
            $message = "Employee record not found.";
        }
    }
}

// If there's an error or failure, redirect back with an error message
if (isset($message)) {
    header("Location: profile.php?message=" . urlencode($message));
    exit();
}

?>
