<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['employee_id']) || empty($_POST['employee_id'])) {
        die("Error: Missing employee ID.");
    }

    $employee_id = intval($_POST['employee_id']); // Convert to integer

    // Check if employee_id exists
    $check_sql = "SELECT employee_id FROM employee_info WHERE employee_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        die("Error: Employee ID does not exist.");
    }
    $stmt->close();

    // Generate random fingerprint ID (Replace this with actual scanner data)
    $fingerprint_id = rand(10000, 99999);

    // Check if the employee already has a fingerprint registered
    $check_fingerprint = "SELECT * FROM employee_fingerprints WHERE employee_id = ?";
    $stmt = $conn->prepare($check_fingerprint);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Error: Fingerprint already enrolled.");
    }
    $stmt->close();

    // Insert fingerprint into the database
    $insert_sql = "INSERT INTO employee_fingerprints (employee_id, fingerprint_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ii", $employee_id, $fingerprint_id);

    if ($stmt->execute()) {
        echo "success: " . $fingerprint_id;
    } else {
        echo "Error enrolling fingerprint.";
    }

    $stmt->close();
    $conn->close();
}
?>
