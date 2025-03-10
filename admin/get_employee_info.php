<?php
include '../config.php';

if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    // Fetch employee info and join with shifts
    $sql = "
        SELECT 
            e.employee_name, 
            e.position, 
            d.department_name, 
            s.shift_name, 
            s.shift_start, 
            s.shift_end
        FROM employee_info e
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN employee_shifts es ON e.employee_id = es.employee_id
        LEFT JOIN shift_types s ON es.shift_type_id = s.shift_type_id
        WHERE e.employee_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employeeData = $result->fetch_assoc();
        echo json_encode($employeeData);
    } else {
        echo json_encode(null); // No employee found
    }

    $stmt->close();
}
$conn->close();
?>
