<?php
include '../config.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch employee shifts
$sql = "SELECT es.employee_shift_id, es.employee_id, ei.employee_name, st.shift_name, st.shift_start, st.shift_end, es.notes 
        FROM employee_shifts es 
        JOIN employee_info ei ON es.employee_id = ei.employee_id 
        JOIN shift_types st ON es.shift_type_id = st.shift_type_id";

$result = $conn->query($sql);

// Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shift - Time and Attendance</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/manage-shifts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="light-mode">
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
    <h2>Employee Shifts</h2>
    <button class="add-shift-button" id="addShiftBtn">Add Employee Shift</button>
    
    <table class="shifts-table">
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Shift Type</th>
            <th>Shift Start</th>
            <th>Shift End</th>
            <th>Notes</th>
            <th>Action</th>

        </tr>
    </thead>
    <tbody>
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['employee_id']}</td>
                <td>{$row['employee_name']}</td>
                <td>{$row['shift_name']}</td>
                <td>{$row['shift_start']}</td>
                <td>{$row['shift_end']}</td>
                <td>{$row['notes']}</td>
                <td>
                    <button class=\"action-icon delete-icon\" data-shift-id=\"{$row['employee_shift_id']}\">
                        <i class=\"fas fa-trash\"></i> <!-- Font Awesome trash can icon -->
                    </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>No shifts found</td></tr>";
}
?>
</tbody>

</table>

</main>

<!-- Custom Confirmation Dialog for Deleting -->
<div id="delete-dialog-overlay" class="delete-dialog-overlay" style="display: none;">
    <div class="delete-dialog-content">
        <h3>Are you sure you want to delete this shift?</h3>
        <div class="delete-dialog-buttons">
            <button id="confirm-delete-button">Delete</button>
            <button class="cancel" id="cancel-delete-button">Cancel</button>
        </div>
    </div>
</div>

<script>
    let deleteShiftId = null; // Variable to store the shift ID to delete

    // Handle the delete button click
    document.querySelectorAll('.action-icon.delete-icon').forEach(button => {
        button.onclick = function() {
            deleteShiftId = this.getAttribute('data-shift-id'); // Get the shift ID
            document.getElementById('delete-dialog-overlay').style.display = 'flex'; // Show the confirmation dialog
        };
    });

    // Handle the confirmation of deletion
    document.getElementById('confirm-delete-button').onclick = function() {
        if (deleteShiftId !== null) {
            fetch(`e-delete_shift.php?shift_id=${deleteShiftId}`, {
                method: 'GET',
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // Show success message
                location.reload(); // Reload the page to reflect the changes
            })
            .catch(error => console.error('Error:', error));
        }
        document.getElementById('delete-dialog-overlay').style.display = 'none'; // Close the dialog
    };

    // Handle cancel button click
    document.getElementById('cancel-delete-button').onclick = function() {
        deleteShiftId = null;
        document.getElementById('delete-dialog-overlay').style.display = 'none'; // Close the dialog
    };

    // Close overlay if user clicks outside of the content
    window.onclick = function(event) {
        const overlay = document.getElementById('delete-dialog-overlay');
        if (event.target === overlay) {
            overlay.style.display = 'none'; // Close the dialog
        }
    };
</script>


<style>
/* Delete icon button styling */
.delete-icon {
    background-color: transparent; /* Remove background */
    color: #dc3545; /* Red color for the icon */
    border: none; /* Remove border */
    cursor: pointer; /* Pointer on hover */
    font-size: 18px; /* Set icon size to 12px */

}

/* Hover effect for the delete icon button */
.delete-icon:hover {
    color: #c82333; /* Darker red on hover */
}

/* Optional: Add a slight scale effect on hover for both buttons */
.action-icon:hover {
    transform: scale(1.1); /* Slight zoom effect */
    transition: transform 0.2s ease-in-out; /* Smooth transition */
}

/* Delete button styling */
.delete-btn { 
    background-color: transparent; /* Remove white background */
    color: #dc3545; /* Red color for text */
    border: 2px solid #dc3545; /* Border same as delete color */
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer; /* Pointer on hover */
}

/* Hover effect for the delete button */
.delete-btn:hover {
    background-color: #dc3545; /* Red background on hover */
    color: white; /* White text on hover */
}


/* Base Style for the overlay */
.delete-dialog-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    display: none; /* Initially hidden */
    justify-content: center; /* Horizontally center the content */
    align-items: center; /* Vertically center the content */
    z-index: 9999; /* Ensure it's on top */
}

/* Base Style for the content of the dialog */
.delete-dialog-content {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
    width: 300px; /* Set a fixed width for the dialog */
    max-width: 90%; /* Ensure it's responsive on small screens */
}

/* Heading style */
.delete-dialog-content h3 {
    font-size: 18px;
    margin-bottom: 20px;
}

/* Style for the buttons */
.delete-dialog-buttons {
    display: flex;
    justify-content: space-around;
}

.delete-dialog-buttons button {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.delete-dialog-buttons button:hover {
    background-color: #f1f1f1; /* Slight hover effect */
}

#confirm-delete-button {
    background-color: red;
    color: white;
}

#confirm-delete-button:hover {
    background-color: darkred;
}

#cancel-delete-button {
    background-color: gray;
    color: white;
}

#cancel-delete-button:hover {
    background-color: darkgray;
}

/* Dark Mode Styles */
body.dark-mode .delete-dialog-overlay {
    background-color: rgba(0, 0, 0, 0.7); /* Darker overlay */
}

body.dark-mode .delete-dialog-content {
    background-color: #2c2c2c; /* Dark background for dialog */
    color: white; /* Text color for dark mode */
}

body.dark-mode .delete-dialog-buttons button {
    color: white; /* Button text color in dark mode */
}

body.dark-mode #confirm-delete-button {
    background-color: #ff4d4d; /* Lighter red */
}

body.dark-mode #confirm-delete-button:hover {
    background-color: #ff1a1a; /* Darker red on hover */
}

body.dark-mode #cancel-delete-button {
    background-color: #555; /* Dark gray */
}

body.dark-mode #cancel-delete-button:hover {
    background-color: #333; /* Darker gray on hover */
}


</style>



<!-- Employee Shift Overlay -->
<div id="employee-shift-overlay" class="shift-overlay">
    <div class="shift-overlay-content">
        <span class="close" id="closeOverlay">&times;</span>
        <h3>Add Employee Shift</h3>
        <form id="addShiftForm">
            <label for="employee_id">Employee ID:</label>
            <input type="number" id="employee_id" name="employee_id" required>
            
            <label for="shift_type_id">Shift Type:</label>
            <select id="shift_type_id" name="shift_type_id" required>
                <!-- Options will be dynamically populated -->
                <option value="">Select Shift Type</option>
                <?php
                // Fetching shift types to populate the dropdown
                $shiftTypesSql = "SELECT shift_type_id, shift_name FROM shift_types";
                $shiftTypesResult = $conn->query($shiftTypesSql);
                if ($shiftTypesResult->num_rows > 0) {
                    while ($type = $shiftTypesResult->fetch_assoc()) {
                        echo "<option value='{$type['shift_type_id']}'>{$type['shift_name']}</option>";
                    }
                }
                ?>
            </select>
            
            <label for="shift_start">Shift Start:</label>
            <input type="time" id="shift_start" name="shift_start" readonly>

            <label for="shift_end">Shift End:</label>
            <input type="time" id="shift_end" name="shift_end" readonly>

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes" required></textarea>
            
            <button type="submit">Add Shift</button>
        </form>
    </div>
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
    <p>2024 Shift Management</p>
</footer>

<script src="../js/sign_out.js"></script>
<script src="../js/no-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
<script src="../js/employee-shift.js"></script>
</body>

<style>

</style>
<script>// employee-shift.js

document.getElementById('addShiftBtn').onclick = function() {
    document.getElementById('employee-shift-overlay').style.display = 'block';
}

document.getElementById('closeOverlay').onclick = function() {
    document.getElementById('employee-shift-overlay').style.display = 'none';
}

// Close overlay if user clicks outside of the content
window.onclick = function(event) {
    const overlay = document.getElementById('employee-shift-overlay');
    if (event.target === overlay) {
        overlay.style.display = 'none';
    }
}

// Handle form submission
document.getElementById('addShiftForm').onsubmit = function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    
    fetch('add_shift.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Display success message
        location.reload(); // Reload the page to see the new shift
    })
    .catch(error => console.error('Error:', error));
}

// New code to fetch and display shift times
document.getElementById('shift_type_id').onchange = function() {
    const shiftTypeId = this.value;

    if (shiftTypeId) {
        fetch(`get_shift_times.php?shift_type_id=${shiftTypeId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('shift_start').value = data.shift_start;
                document.getElementById('shift_end').value = data.shift_end;
            })
            .catch(error => console.error('Error:', error));
    } else {
        // Clear the times if no shift type is selected
        document.getElementById('shift_start').value = '';
        document.getElementById('shift_end').value = '';
    }
}

</script>
</html>
