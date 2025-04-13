<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
include '../config.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']); // employee_no
    $shift_type_id = intval($_POST['shift_type_id']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    // Check if employee already has a shift
    $checkSql = "SELECT employee_shift_id FROM emp_shifts WHERE employee_id = '$employee_id'";
    $checkResult = mysqli_query($conn, $checkSql);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $row = mysqli_fetch_assoc($checkResult);
        $existingShiftId = $row['employee_shift_id'];

        // Update shift
        $updateSql = "UPDATE emp_shifts SET shift_type_id = $shift_type_id, notes = '$notes' WHERE employee_shift_id = $existingShiftId";
        if (mysqli_query($conn, $updateSql)) {
            echo "Shift updated successfully.";
        } else {
            echo "Error updating shift: " . mysqli_error($conn);
        }
    } else {
        // Insert new shift
        $insertSql = "INSERT INTO emp_shifts (employee_id, shift_type_id, notes) VALUES ('$employee_id', $shift_type_id, '$notes')";
        if (mysqli_query($conn, $insertSql)) {
            echo "Shift added successfully.";
        } else {
            echo "Error inserting shift: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
?>
