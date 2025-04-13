<?php
header('Content-Type: application/json');
include '../config.php';

$cutoff_start = $_GET['cutoff_start'] ?? null;
$cutoff_end = $_GET['cutoff_end'] ?? null;

if (!$cutoff_start || !$cutoff_end) {
    echo json_encode([
        "error" => "cutoff_start and cutoff_end are required in the query parameters."
    ]);
    exit;
}

// Fetch timesheet data grouped by employee within the date range
$sql = "SELECT employee_id, COUNT(*) as total_days 
        FROM employee_timesheet 
        WHERE DATE(time_in) BETWEEN ? AND ?
        GROUP BY employee_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $cutoff_start, $cutoff_end);
$stmt->execute();
$result = $stmt->get_result();

$response = [];

while ($row = $result->fetch_assoc()) {
    $response[] = [
        "employee_id" => $row['employee_id'],
        "total_days" => (int)$row['total_days'],
        "total_holiday" => 0, // Default to 0 unless you have holiday logic
        "cutoff_start" => $cutoff_start,
        "cutoff_end" => $cutoff_end
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
