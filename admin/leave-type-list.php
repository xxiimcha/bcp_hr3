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
<div class="top-nav">
    <ul>
        <a href="../maindashboard.php">
            <h1 class="logopos">
                
                Paradise <br> Hotel
            </h1>
        </a>
        <li class="top">
            <a class="top1" href="">
                <i class="fas fa-home"></i> <!-- Icon for Home -->
                Home
            </a>
            <div class="top1dropdown">
                <div class="dropdown-column">
                    <h3>Payroll</h3> <!-- Icon for Payroll -->
                    <a href="time-and-attendance-home.php">
                        <i class="fas fa-clock"></i> Time and Attendance <!-- Icon for Time and Attendance -->
                    </a>
                    <a href="../Employee-information/employee-list.php">
                        <i class="fas fa-users"></i> Employee Information <!-- Icon for Employee Information -->
                    </a>
                    <a href="payroll/log-in.php">
                        <i class="fas fa-calculator"></i> Payroll Processing <!-- Icon for Payroll Processing -->
                    </a>
                </div>           
            </div>
        </li>
        <li class="top">
            <a class="top1" href="time-and-attendance-home.php">
                <i class="fas fa-chart-line"></i> <!-- Icon for Dashboard -->
                Dashboard
            </a>          
        </li>
        <li class="top">
            <a class="top1" href="">
                <i class="fas fa-tasks"></i> <!-- Icon for Manage -->
                Manage
            </a>
            <div class="top1dropdown">
                <div class="dropdown-column">
                    <h3><b>Attendance Tracking</b></h3> <!-- Icon for Attendance Tracking -->
                    <a href="clocking-system.php">
                        <i class="fas fa-clock"></i> Clocking System <!-- Icon for Clocking System -->
                    </a>
                    <a href="timesheet.php">
                        <i class="fas fa-calendar-alt"></i> Daily Record <!-- Icon for Daily Record -->
                    </a>
                    <a href="attendance-summary.php">
                        <i class="fas fa-list"></i> Attendance Summary <!-- Icon for Attendance Summary -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Leave Management</b></h3> <!-- Icon for Leave Management -->
                    <a href="leavemanagement.php">
                        <i class="fas fa-envelope-open-text"></i> Leave Requests <!-- Icon for Leave Requests -->
                    </a>
                    <a href="leave-record.php">
                        <i class="fas fa-file-alt"></i> Employee Leave Records <!-- Icon for Leave Records -->
                    </a>
                    <a href="leave-type-list.php">
                        <i class="fas fa-list-alt"></i> List of Leave Types <!-- Icon for Leave Types -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Shift Management</b></h3> <!-- Icon for Shift Management -->
                    <a href="manage-shift.php">
                        <i class="fas fa-calendar"></i> Manage Shift <!-- Icon for Manage Shift -->
                    </a>
                    <a href="shift-types.php">
                        <i class="fas fa-layer-group"></i> Shift Types <!-- Icon for Shift Types -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Compliance & Labor Law Adherence</b></h3> <!-- Icon for Compliance -->
                    <a href="../admin/compliance/violations.php">
                        <i class="fas fa-exclamation-triangle"></i> Violations <!-- Icon for Violations -->
                    </a>
                    <a href="../admin/compliance/compliance-report.php">
                        <i class="fas fa-file-contract"></i> Compliance Report <!-- Icon for Compliance Report -->
                    </a>
                    <a href="../admin/compliance/labor-policies.php">
                        <i class="fas fa-book"></i> Labor Policies <!-- Icon for Labor Policies -->
                    </a>
                    <a href="../admin/compliance/adherence-monitoring.php">
                        <i class="fas fa-eye"></i> Adherence Monitoring <!-- Icon for Monitoring -->
                    </a>
                </div>
            </div>
        </li>
        <li class="top">
            <a class="top1" href="#settings">
                <i class="fas fa-cog"></i> <!-- Icon for Settings -->
                Settings
            </a>

        </li>
    </ul>
    <button type="button" id="darkModeToggle" class="dark-mode-toggle" aria-label="Toggle Dark Mode">
        <i class="fas fa-moon"></i> <!-- Icon for dark mode toggle -->
    </button>

    <!-- USER -->
    <div class="admin-section">
        <div class="admin-name">
            <i class="fas fa-user"></i> User - <?php echo htmlspecialchars($username); ?>
            <div class="admin-dropdown-content">
                <a href="../manage_account.php">Manage Account</a>
            </div>
        </div>
    </div>
    <button type="button" class="logout" id="logout-button" style="margin-right: 10px;">
        <i class="fas fa-sign-out-alt"></i> <!-- Icon for logout -->
    </button>
</div>
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
