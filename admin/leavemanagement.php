<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch "Full-time" employee details for the dropdown
$employeeResult = $conn->query("SELECT employee_id, employee_name FROM employee_info WHERE status = 'Full-time'");

// Fetch leave types for the leave type dropdown
$leaveTypesResult = $conn->query("SELECT leave_id, leave_type FROM leave_types");

// Fetch leave requests to display in the table
$leaveRequestsResult = $conn->query("SELECT e.employee_id, e.employee_name, d.department_name, e.position, 
                                        lr.leave_id, lt.leave_type, lr.start_date, lr.end_date, 
                                        lr.total_days, lr.remarks, lr.status, lr.date_submitted  
                                     FROM employee_leave_requests lr
                                     JOIN employee_info e ON lr.employee_id = e.employee_id
                                     JOIN departments d ON e.department_id = d.department_id
                                     JOIN leave_types lt ON lr.leave_id = lt.leave_id");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/leave_requestss.css">
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

    <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>


    <!-- Hidden Leave Request Form (Modal Overlay) -->
    <div class="overlay" id="overlay" style="display:none;">
        <div class="leave-request-form">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Leave Request Form</h3>
                <button class="close-btn" onclick="closeForm()">X</button>
            </div>

            

            <form method="POST" action="submit-leave-request.php" id="leaveForm">
                <div class="form-row">
                <div class="form-group">
    <label for="employee_id">Fetch:</label>
    <input type="text" id="employee_id" name="employee_id" placeholder="Enter Employee ID" required>
    <button type="button" id="fetchButton" onclick="fetchEmployeeDetails()">Fetch</button>
</div>



                    <div class="form-group">
                        <label for="leave_type">Leave Type:</label>
                        <select id="leave_type" name="leave_type" required>
                            <option value="">Select Leave Type</option>
                            <?php
                            if ($leaveTypesResult->num_rows > 0) {
                                while ($row = $leaveTypesResult->fetch_assoc()) {
                                    echo '<option value="' . $row['leave_id'] . '">' . htmlspecialchars($row['leave_type']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="employee_name">Employee Name:</label>
                        <input type="text" id="employee_name" name="employee_name" readonly>
                    </div>

                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" name="department" readonly>
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="position">Position:</label>
                        <input type="text" id="position" name="position" readonly>
                    </div>

                    <div class="form-group">
                        <label for="total_days">Total Day/s:</label>
                        <input type="number" id="total_days" name="total_days" readonly>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="remarks">Remarks:</label>
                    <textarea id="remarks" name="remarks" rows="4" placeholder="Comment" required></textarea>
                </div>

                <div class="form-row" style="display: none;">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Reject</option>
                        </select>
                    </div>
                </div>

                <center><button type="submit">Submit Leave Request</button></center>
            </form>
        </div>
    </div>
</div>



<div class="panel">

<div class="flex-container">
    <h3>Pending Request</h3>
    <button type="button" class="view" id="viewListButton">
    <i class="fas fa-plus"></i>
</button>
        </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Date Submitted</th>
                <th>Remarks</th>
                <th>Status</th>
                <th>Change Status</th>

            </tr>
        </thead>
       <tbody>
    <?php
    if ($leaveRequestsResult->num_rows > 0) {
        while ($row = $leaveRequestsResult->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['employee_id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['employee_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['position']) . '</td>';
            echo '<td>' . htmlspecialchars($row['leave_type']) . '</td>';
            echo '<td>' . htmlspecialchars($row['start_date']) . '</td>';
            echo '<td>' . htmlspecialchars($row['end_date']) . '</td>';
            echo '<td>' . htmlspecialchars($row['total_days']) . '</td>';
            echo '<td>' . htmlspecialchars($row['date_submitted']) . '</td>';
            echo '<td>' . htmlspecialchars($row['remarks']) . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            
            echo '<td><form method="POST" action="update-leave-status.php" class="form-update">
                        <input type="hidden" name="employee_id" value="' . htmlspecialchars($row['employee_id']) . '">
                        <input type="hidden" name="leave_id" value="' . htmlspecialchars($row['leave_id']) . '">
                        <input type="hidden" name="start_date" value="' . htmlspecialchars($row['start_date']) .'">
                        <input type="hidden" name="end_date" value="' .  htmlspecialchars($row['end_date']) . '">

                        <input type="hidden" name="total_days" value="' . htmlspecialchars($row['total_days']) . '">
                        <select name="status" required>
                            <option value="" disabled selected>Select Status</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                        <button type="submit" class="update" >Update</button>
                    </form></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="12">No leave requests found.</td></tr>';
    }
    ?>
</tbody>
    </table>
</div>

<script>
    const viewListButton = document.getElementById('viewListButton');
    const overlay = document.getElementById('overlay');

    viewListButton.addEventListener('click', () => {
        overlay.style.display = 'flex';
    });

    function closeForm() {
        overlay.style.display = 'none';
    }
</script>

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


    <script src="../js/fetch&calculate_leave.js"></script>
    <script src="../js/no-previousbutton.js"></script>
    <script src="../js/sign_out.js"></script>
<script src="../js/toggle-darkmode.js"></script>


<script>function fetchEmployeeDetails() {
    const employeeId = document.getElementById('employee_id').value.trim(); // Trim input to avoid extra spaces

    // Clear fields if input is empty
    if (!employeeId) {
        clearEmployeeDetails();
        alert('Please enter an Employee ID.');
        return;
    }

    // Use AJAX to fetch employee details
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_employee_details.php?employee_id=' + encodeURIComponent(employeeId), true); // Encode the input for safety

    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                const employeeData = JSON.parse(xhr.responseText);

                if (employeeData && employeeData.employee_name) {
                    // Populate fields with fetched data
                    document.getElementById('employee_name').value = employeeData.employee_name || '';
                    document.getElementById('department').value = employeeData.department_name || '';
                    document.getElementById('position').value = employeeData.position || '';
                } else {
                    clearEmployeeDetails(); // Clear fields if no data found
                    alert('No employee details found for the given ID.');
                }
            } catch (error) {
                clearEmployeeDetails(); // Handle invalid JSON response
                console.error('Error parsing employee details:', error);
                alert('Failed to fetch employee details. Please try again.');
            }
        } else {
            clearEmployeeDetails();
            alert('Error fetching details. Status: ' + xhr.status);
        }
    };

    xhr.onerror = function () {
        clearEmployeeDetails();
        alert('An error occurred while fetching employee details.');
    };

    xhr.send();
}

// Function to clear the employee details fields
function clearEmployeeDetails() {
    document.getElementById('employee_name').value = '';
    document.getElementById('department').value = '';
    document.getElementById('position').value = '';
}

</script>
</body>
<style>/* Style for the Fetch button */
#fetchButton {
    display: inline-block;
    margin-top: 10px;
    padding: 7px 15px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    background-color: #007bff; /* Primary blue */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

/* Hover effect for the button */
#fetchButton:hover {
    background-color: #0056b3; /* Darker blue */
    transform: scale(1.05); /* Slightly enlarge the button */
}

/* Active effect for the button */
#fetchButton:active {
    background-color: #003f7f; /* Even darker blue */
    transform: scale(0.95); /* Slightly shrink the button */
}


</style>

</html>