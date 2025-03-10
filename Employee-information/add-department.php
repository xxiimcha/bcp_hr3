<?php
include '../config.php'; // Ensure the path is correct
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../log-in.php");
    exit();
}

// Initialize variables for feedback
$successMessage = "";
$errorMessage = "";

// Get the form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department_id = $_POST['department_id'];
    $department_name = $_POST['department_name'];

    // Check if the department ID already exists
    $checkStmt = $conn->prepare("SELECT department_id FROM departments WHERE department_id = ?");
    $checkStmt->bind_param("i", $department_id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $errorMessage = "Error: Department ID already exists!";
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO departments (department_id, department_name) VALUES (?, ?)");
        $stmt->bind_param("is", $department_id, $department_name);

        // Execute the statement
        if ($stmt->execute()) {
            $successMessage = "Department added successfully!";
        } else {
            // Handle error
            $errorMessage = "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    }

    // Close check statement
    $checkStmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <title>Create Department</title>
    <link rel="stylesheet" href="../css/department.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">   
</head>
    
<body>
<div class="form-container">
    <h2>Add New Department</h2>
    <h4>Form to add new department to system</h4>
    <hr>
    
    <?php if ($successMessage): ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <br>        
    <form action="" method="POST">
        <div class="form-group">
            <label for="department-id">Department ID</label><br>
            <input type="text" id="department-id" name="department_id" required>
        </div>
        
        <div class="form-group">
            <label for="department-name">Department Name</label><br>
            <input type="text" id="department-name" name="department_name" required>
        </div>
        
        <div class="form-group right-align">
            <div class="button-container">
                <button type="button" class="back-button" onclick="window.location.href='department.php'">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="submit" class="add-to-system-btn">Add to System</button>
            </div>
        </div>
    </form>
</div>

<!-- Custom Confirmation Dialog -->
<div id="dialog-overlay" class="dialog-overlay">
    <div class="dialog-content">
        <h3>Are you sure you want to sign out?</h3>
        <div class="dialog-buttons">
            <button id="confirm-button">Sign Out</button>
            <button class="cancel" id="cancel-button">Cancel</button>
        </div>
    </div>
</div>

<footer>
    <p>2024 Department</p>
</footer>
</body>

<script src="../js/sign-out.js"></script>
<script src="../js/toggle-darkmode.js"></script>

</html>
