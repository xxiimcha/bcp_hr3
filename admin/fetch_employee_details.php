<?php
include '../config.php';

if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    // Query to get employee details
    $query = "SELECT employee_name, position, d.department_name 
              FROM employee_info e 
              JOIN departments d ON e.department_id = d.department_id 
              WHERE employee_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        echo json_encode($employee);
    } else {
        echo json_encode(['employee_name' => '', 'position' => '', 'department_name' => '']);
    }

    $stmt->close();
}
$conn->close();
?>
