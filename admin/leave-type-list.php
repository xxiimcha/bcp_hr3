<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch leave types from the database
$queryLeaveTypes = "SELECT leave_code, leave_type, DefaultCredit FROM leave_types"; 
$resultLeaveTypes = $conn->query($queryLeaveTypes);

// Get success message
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']); // Clear the message after displaying

// Get error message
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']); // Clear the message after displaying

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Leave Types</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/manage-leave-type-list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->
<?php include '../partials/nav.php'; ?>
<!-- END OF TOP NAV BAR -->
<main><br>
    <h1>List of Leave Types</h1><br>

    <!-- Success and Error Messages -->
    <?php if ($successMessage): ?>
        <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <!-- Search Bar -->
    <center> 
        <input type="text" id="searchInput" placeholder="Search by Leave Code, Leave Type" onkeyup="filterTable()">

    <!-- Button Wrapper for Alignment -->
    <div class="button-wrapper">
        <button class="create-leave-type-button" onclick="openCreateLeaveTypeModal()">Create New Leave Type</button>
    </div>

    <!-- Leave Type Table -->
    <table class="leave-type-table" id="leaveTypeTable">
        <thead>
            <tr>
                <th>Leave Code</th>
                <th>Leave Type</th>
                <th>Default Credit</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultLeaveTypes && $resultLeaveTypes->num_rows > 0): ?>
                <?php while ($row = $resultLeaveTypes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['leave_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['DefaultCredit']); ?></td>
                        <td>
                            <button class="edit-button" onclick="openEditModal('<?php echo htmlspecialchars($row['leave_code']); ?>', '<?php echo htmlspecialchars($row['leave_type']); ?>', <?php echo htmlspecialchars($row['DefaultCredit']); ?>)">Edit</button>
                            <button class="delete-button" onclick="confirmDelete('<?php echo htmlspecialchars($row['leave_type']); ?>', '<?php echo htmlspecialchars($row['leave_code']); ?>')">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No leave types found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </center>

</main>

<!-- Create Leave Type Modal -->
<div id="createLeaveTypeModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Create New Leave Type</h2>
        <form action="create-leave-type.php" method="POST">
            <label for="leave_code">Leave Code:</label>
            <input type="text" id="leave_code" name="leave_code" required>

            <label for="leave_type">Leave Type:</label>
            <input type="text" id="leave_type" name="leave_type" required>

            <label for="DefaultCredit">Default Credit:</label>
            <input type="number" id="DefaultCredit" name="DefaultCredit" required>

            <button type="submit" class="submit-button">Create Leave Type</button>
            <button type="button" class="cancel-button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- Edit Leave Type Modal -->
<div id="editLeaveTypeModal" class="edit-modal-overlay" style="display:none;">
    <div class="edit-modal-content">
        <h2>Edit Leave Type</h2>
        <form action="edit-leave-type.php" method="POST">
            <label for="edit_leave_code">Leave Code:</label>
            <input type="text" id="edit_leave_code" name="leave_code" required readonly>

            <label for="edit_leave_type">Leave Type:</label>
            <input type="text" id="edit_leave_type" name="leave_type" required>

            <label for="edit_DefaultCredit">Default Credit:</label>
            <input type="number" id="edit_DefaultCredit" name="DefaultCredit" required>
            
            <button type="submit" class="submit-button">Update Leave Type</button>
            <button type="button" class="cancel-button" onclick="closeEditModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Dialog -->
<div id="deleteConfirmationDialog" class="delelete-dialog-overlay1" style="display:none;">
    <div class="delete-dialog-content">
        <h3>Are you sure you want to delete Leave Type: <span id="leave-type-display"></span>?</h3>
        <div class="dialog-buttons">
            <button id="confirm-delete-button">Yes, Delete</button>
            <button class="cancel" id="cancel-delete-button">Cancel</button>
        </div>
    </div>
</div>

<footer>
    <p>2024 Leave Management</p>
</footer>

<script src="../js/sign_out.js"></script>
<script src="../js/no-previousbutton.js"></script>
<script src="../js/crud-leave-type.js"></script>
<script src="../js/toggle-darkmode.js"></script>
</body>
</html>
