<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Loop through the incoming data
    parse_str(file_get_contents("php://input"), $postData); // Decode incoming data

    if (isset($postData['attendance_id'])) {
        // Prepare the query to insert or update attendance summary
        $attendance_id = intval($postData['attendance_id']);
        $worked_hours = floatval($postData['worked_hours']);
        $total_overtime_hours = floatval($postData['total_overtime_hours']);

        // Check if the record already exists
        $checkSql = "SELECT summary_id FROM attendance_summary WHERE attendance_id = $attendance_id";
        $result = $conn->query($checkSql);

        if ($result && $result->num_rows > 0) {
            // Update existing record
            $updateSql = "UPDATE attendance_summary 
                          SET worked_hours = ?, total_overtime_hours = ? 
                          WHERE attendance_id = ?";

            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param('ddi', $worked_hours, $total_overtime_hours, $attendance_id);
            $stmt->execute();
            echo json_encode(['status' => 'success']);
        } else {
            // Insert new record
            $insertSql = "INSERT INTO attendance_summary (attendance_id, worked_hours, total_overtime_hours)
                          VALUES (?, ?, ?)";

            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param('ddi', $attendance_id, $worked_hours, $total_overtime_hours);
            $stmt->execute();
            echo json_encode(['status' => 'success']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    }
}

$conn->close();
?>
