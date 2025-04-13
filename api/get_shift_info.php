<?php
include '../config.php';
header('Content-Type: application/json');

$employee_id = $_GET['employee_id'] ?? '';

if (empty($employee_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing employee_id'
    ]);
    exit;
}

$employee_id = mysqli_real_escape_string($conn, $employee_id);

// Example: Join with shift_types if you have one
$query = "
    SELECT 
        s.shift_type_id,
        st.type_name AS shift_type,
        st.start_time AS shift_start,
        st.end_time AS shift_end
    FROM emp_shifts s
    LEFT JOIN shift_types st ON s.shift_type_id = st.id
    WHERE s.employee_id = '$employee_id'
    ORDER BY s.updated_at DESC
    LIMIT 1
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    echo json_encode([
        'status' => 'success',
        'shift_type_id' => $row['shift_type_id'],
        'shift_type' => $row['shift_type'] ?? 'N/A',
        'shift_start' => $row['shift_start'] ?? 'N/A',
        'shift_end' => $row['shift_end'] ?? 'N/A',
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No shift data found for this employee'
    ]);
}

mysqli_close($conn);
