<?php
session_start(); // Start the session

if (isset($_SESSION['employee_id'])) {
    // Unset only the session variables related to the currently logged-in employee
    unset($_SESSION['employee_id']);  // Remove the employee_id from the session
    unset($_SESSION['logged_in']);    // Remove the logged_in status (if you have one for tracking login status)

    // Keep other session data intact, do not use session_destroy()

    // Redirect to the login page or home page after logging out
    header("Location: ../index.php");
    exit();
} else {
    // If no employee is logged in, simply redirect to the login page
    header("Location: ../index.php");
    exit();
}
