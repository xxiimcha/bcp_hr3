<?php
include '../config.php';

if ($_POST['action'] == 'enroll') {
    $employee_id = $_POST['employee_id'] ?? '';
    $fingerprint_id = $_POST['fingerprint_id'] ?? '';

    if (empty($employee_id) || empty($fingerprint_id)) {
        echo "Missing data.";
        exit;
    }

    $employee_id = mysqli_real_escape_string($conn, $employee_id);
    $fingerprint_id = mysqli_real_escape_string($conn, $fingerprint_id);

    $sql = "INSERT INTO employee_fingerprints (employee_id, fingerprint_id) 
            VALUES ('$employee_id', '$fingerprint_id')";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "failed: " . mysqli_error($conn);
    }
}
