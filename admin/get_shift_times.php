<?php
include '../config.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

if (isset($_GET['shift_type_id'])) {
    $shift_type_id = intval($_GET['shift_type_id']);
    
    $sql = "SELECT shift_start, shift_end FROM shift_types WHERE shift_type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shift_type_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row); // Return the start and end times as JSON
    } else {
        echo json_encode(['shift_start' => '', 'shift_end' => '']);
    }
    
    $stmt->close();
}

$conn->close();
?>
