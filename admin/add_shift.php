<?php
include '../config.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $shift_type_id = $_POST['shift_type_id'];
    $notes = $_POST['notes'];

    // Optional: Validate inputs here

    // Check if the employee exists
    $employeeCheckStmt = $conn->prepare("SELECT employee_id FROM employee_info WHERE employee_id = ?");
    $employeeCheckStmt->bind_param("i", $employee_id);
    $employeeCheckStmt->execute();
    $employeeCheckStmt->store_result();

    if ($employeeCheckStmt->num_rows == 0) {
        // Employee not found
        echo "Error: Employee not found.";
    } else {
        // Employee exists, check if the employee already has a shift
        $checkStmt = $conn->prepare("SELECT employee_shift_id FROM employee_shifts WHERE employee_id = ?");
        $checkStmt->bind_param("i", $employee_id);
        $checkStmt->execute();
        $checkStmt->bind_result($existingShiftId);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($existingShiftId) {
            // Update the existing shift
            $updateStmt = $conn->prepare("UPDATE employee_shifts SET shift_type_id = ?, notes = ? WHERE employee_shift_id = ?");
            $updateStmt->bind_param("ssi", $shift_type_id, $notes, $existingShiftId);

            if ($updateStmt->execute()) {
                echo "Shift updated successfully.";
            } else {
                echo "Error: " . $updateStmt->error;
            }

            $updateStmt->close();
        } else {
            // No existing shift, insert a new one
            $insertStmt = $conn->prepare("INSERT INTO employee_shifts (employee_id, shift_type_id, notes) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iis", $employee_id, $shift_type_id, $notes);

            if ($insertStmt->execute()) {
                echo "Shift added successfully.";
            } else {
                echo "Error: " . $insertStmt->error;
            }

            $insertStmt->close();
        }
    }

    $employeeCheckStmt->close();
}

$conn->close();
?>
