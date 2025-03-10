<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the employee ID from the POST request
    $employee_id = $_POST['employee_id'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT employee_name, position, department_id FROM employee_info WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id); // Assuming employee_id is a string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch employee data
        $employee_data = $result->fetch_assoc();

        // Fetch department name
        $department_id = $employee_data['department_id'];
        $dept_stmt = $conn->prepare("SELECT department_name FROM departments WHERE department_id = ?");
        $dept_stmt->bind_param("s", $department_id);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        $department_name = $dept_result->fetch_assoc()['department_name'];

        // Return the employee details as JSON
        echo json_encode([
            'employee_name' => $employee_data['employee_name'],
            'position' => $employee_data['position'],
            'department_name' => $department_name
        ]);
    } else {
        // Return error message if employee not found
        echo json_encode(['error' => 'Employee not found.']);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
