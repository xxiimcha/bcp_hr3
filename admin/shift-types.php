<?php
include '../config.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete') {
            // Handle deletion of a shift type
            $shift_type_id = $_POST['shift_type_id'];

            $stmt = $conn->prepare("DELETE FROM shift_types WHERE shift_type_id = ?");
            $stmt->bind_param("i", $shift_type_id);

            if ($stmt->execute()) {
                $message = "Shift deleted successfully!"; // Set success message
            } else {
                $message = "Error deleting shift: " . $stmt->error; // Set error message
            }

            $stmt->close();
        } elseif ($_POST['action'] === 'update') {
            // Handle updating a shift
            $shift_type_id = $_POST['shift_type_id'];
            $shift_name = $_POST['shiftName'];
            $shift_start = $_POST['shiftStart'];
            $shift_end = $_POST['shiftEnd'];

            // Prepare and bind for update
            $stmt = $conn->prepare("UPDATE shift_types SET shift_name = ?, shift_start = ?, shift_end = ? WHERE shift_type_id = ?");
            $stmt->bind_param("sssi", $shift_name, $shift_start, $shift_end, $shift_type_id);

            if ($stmt->execute()) {
                $message = "Shift updated successfully!"; // Set success message
            } else {
                $message = "Error updating shift: " . $stmt->error; // Set error message
            }

            $stmt->close();
        }
    } else {
        // Handle adding a new shift
        $shift_name = $_POST['shiftName'];
        $shift_start = $_POST['shiftStart'];
        $shift_end = $_POST['shiftEnd'];

        // Check for existing shift name
        $stmt = $conn->prepare("SELECT * FROM shift_types WHERE shift_name = ?");
        $stmt->bind_param("s", $shift_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Shift already exists!"; // Set duplication message
        } else {
            // Prepare and bind for insert
            $stmt = $conn->prepare("INSERT INTO shift_types (shift_name, shift_start, shift_end) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $shift_name, $shift_start, $shift_end);

            if ($stmt->execute()) {
                $message = "Shift added successfully!"; // Set success message
            } else {
                $message = "Error adding shift: " . $stmt->error; // Set error message
            }
        }

        $stmt->close();
    }
}

// Fetching all shift types
$shift_types = [];
$sql = "SELECT * FROM shift_types";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $shift_types[] = $row;
    }
} else {
    echo "No shift types found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shift - Time and Attendance</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/shift-type.css">
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

<main>
    <!-- Main Content -->
    <h1>Manage Shift Types</h1>

    <button id="addShiftButton" class="shift-button">Add Shift</button>

    <!-- Success/Error Message -->
    <?php if ($message): ?>
        <div id="statusMessage" class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <table class="shift-types-table">
    <thead>
        <tr>
            <th>Shift Name</th>
            <th>Shift Start</th>
            <th>Shift End</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($shift_types as $shift): ?>
        <tr>
            <td><?php echo htmlspecialchars($shift['shift_name']); ?></td>
            <td><?php echo htmlspecialchars($shift['shift_start']); ?></td>
            <td><?php echo htmlspecialchars($shift['shift_end']); ?></td>
            <td>
                <button class="update-button" onclick="openUpdateOverlay(<?php echo htmlspecialchars($shift['shift_type_id']); ?>, '<?php echo htmlspecialchars($shift['shift_name']); ?>', '<?php echo htmlspecialchars($shift['shift_start']); ?>', '<?php echo htmlspecialchars($shift['shift_end']); ?>')">Update</button>
                <button class="delete-button" onclick="confirmDelete(<?php echo htmlspecialchars($shift['shift_type_id']); ?>)">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    </table>

    <!-- Shift Overlay for Adding a Shift -->
    <div id="shiftOverlay" class="shift-overlay" style="display: none;">
        <div class="shift-overlay-content">
            <span class="close-button" id="closeOverlay">&times;</span>
            <h2>Add Shift</h2>
            <form id="addShiftForm" method="POST">
                <label for="shiftName">Shift Name:</label>
                <input type="text" id="shiftName" name="shiftName" required>

                <label for="shiftStart">Shift Start:</label>
                <input type="time" id="shiftStart" name="shiftStart" required>

                <label for="shiftEnd">Shift End:</label>
                <input type="time" id="shiftEnd" name="shiftEnd" required>

                <button type="submit" class="add-shift-button">Add Shift</button>
            </form>
        </div>
    </div>

    <!-- Shift Overlay for Updating a Shift -->
    <div id="updateOverlay" class="shift-overlay" style="display: none;">
        <div class="shift-overlay-content">
            <span class="close-button" id="closeUpdateOverlay">&times;</span>
            <h2>Update Shift</h2>
            <form id="updateShiftForm" method="POST">
                <input type="hidden" id="updateShiftTypeId" name="shift_type_id">
                <label for="updateShiftName">Shift Name:</label>
                <input type="text" id="updateShiftName" name="shiftName" required>

                <label for="updateShiftStart">Shift Start:</label>
                <input type="time" id="updateShiftStart" name="shiftStart" required>

                <label for="updateShiftEnd">Shift End:</label>
                <input type="time" id="updateShiftEnd" name="shiftEnd" required>

                <button type="submit" class="update-shift-button">Update Shift</button>
                <input type="hidden" name="action" value="update">
            </form>
        </div>
    </div>
</main>


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
<!-- Custom Confirmation Dialog -->
<div id="delete-dialog-overlay" class="delete-dialog-overlay" style="display: none;">
    <div class="delete-dialog-content">
        <h3>Are you sure you want to delete this shift?</h3>
        <div class="dialog-buttons">
            <button id="confirm-delete-button">Delete</button>
            <button class="cancel" id="cancel-delete-button">Cancel</button>
        </div>
    </div>
</div>


<footer>
    <p>2024 Shift Management</p>
</footer>

<script src="../js/sign_out.js"></script>
<script src="../js/no-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>

<script>

<!-- Add this JavaScript at the bottom of your <body> tag or in a separate JS file -->

    let shiftToDeleteId = null; // Variable to hold the ID of the shift to delete

    function confirmDelete(shift_type_id) {
        shiftToDeleteId = shift_type_id; // Store the shift ID to delete
        document.getElementById('delete-dialog-overlay').style.display = 'flex'; // Show delete confirmation dialog
    }

    document.getElementById('confirm-delete-button').addEventListener('click', function() {
        if (shiftToDeleteId !== null) {
            // Create a form to submit the delete action
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Submit to the current page

            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'shift_type_id';
            inputId.value = shiftToDeleteId;
            form.appendChild(inputId);

            const inputAction = document.createElement('input');
            inputAction.type = 'hidden';
            inputAction.name = 'action';
            inputAction.value = 'delete';
            form.appendChild(inputAction);

            document.body.appendChild(form); // Append the form to the body
            form.submit(); // Submit the form
        }
    });

    document.getElementById('cancel-delete-button').addEventListener('click', function() {
        document.getElementById('delete-dialog-overlay').style.display = 'none'; // Hide the delete confirmation dialog
        shiftToDeleteId = null; // Clear the shift ID
    });


// Cancel deletion action
document.getElementById('cancel-delete-button').onclick = function() {
    document.getElementById('delete-dialog-overlay').style.display = 'none'; // Hide confirmation dialog
};


// Open Add Shift Overlay
document.getElementById('addShiftButton').onclick = function() {
    document.getElementById('shiftOverlay').style.display = 'flex'; // Show add shift overlay
};

// Close Add Shift Overlay
document.getElementById('closeOverlay').onclick = function() {
    document.getElementById('shiftOverlay').style.display = 'none'; // Hide add shift overlay
};

// Open Update Overlay
function openUpdateOverlay(id, name, start, end) {
    document.getElementById('updateShiftTypeId').value = id; // Set shift type ID
    document.getElementById('updateShiftName').value = name; // Set shift name
    document.getElementById('updateShiftStart').value = start; // Set shift start
    document.getElementById('updateShiftEnd').value = end; // Set shift end
    document.getElementById('updateOverlay').style.display = 'flex'; // Show update overlay
}

// Close Update Overlay
document.getElementById('closeUpdateOverlay').onclick = function() {
    document.getElementById('updateOverlay').style.display = 'none'; // Hide update overlay
};
</script>

</body>
</html>
