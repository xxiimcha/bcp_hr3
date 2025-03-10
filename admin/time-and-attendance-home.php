<?php

include '../config.php'; // Include your database connection file

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Include the database configuration file

// Fetch counts from the relevant tables
$departmentCount = $conn->query("SELECT COUNT(*) FROM departments")->fetch_row()[0];
$leaveTypeCount = $conn->query("SELECT COUNT(*) FROM leave_types")->fetch_row()[0];
$leaveRequestCount = $conn->query("SELECT COUNT(*) FROM employee_leave_requests")->fetch_row()[0];
$employeeCount = $conn->query("SELECT COUNT(*) FROM employee_info")->fetch_row()[0];
// Fetch the most recent leave requests
$recentLeaveRequests = $conn->query("SELECT * FROM employee_leave_requests ORDER BY date_submitted DESC LIMIT 5");

$todayLeaveRequests = $conn->query("SELECT * FROM employee_leave_requests WHERE DATE(date_submitted) = CURDATE() AND TIME(date_submitted) = CURTIME()");

$employeeShiftsCount = $conn->query("SELECT COUNT(*) FROM employee_shifts")->fetch_row()[0];

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Time and Attendance</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body> <!-- Initially setting light mode -->
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
    <style>



    </style>
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

<main>
    <div class="dashboard-panel">
        <h2 style="text-align: left;">Dashboard</h2>        <hr>
        <br>
        <div class="dashboard-boxes">

            <a href="leave-type-list.php" class="dashboard-box">
                <i class="fas fa-calendar-alt"></i> <!-- Icon for Leave Types -->
                <h3>Leave Types</h3>
                <p><?php echo htmlspecialchars($leaveTypeCount); ?></p>
            </a>
            <a href="leavemanagement.php" class="dashboard-box">
                <i class="fas fa-user-check"></i> <!-- Icon for Leave Requests -->
                <h3>Leave Requests</h3>
                <p><?php echo htmlspecialchars($leaveRequestCount); ?></p>
            </a>
            <a href="../Employee-information/employee-list.php" class="dashboard-box">
                <i class="fas fa-users"></i> <!-- Icon for Employees -->
                <h3>Total Employees</h3>
                <p><?php echo htmlspecialchars($employeeCount); ?></p>
            </a>
            <a href="shift-types.php" class="dashboard-box">
        <i class="fas fa-layer-group"></i> <!-- Icon for Shift Types -->
        <h3>Shifts</h3>
        <p><?php echo htmlspecialchars($employeeShiftsCount); ?></p>
    </a>
        </div>


<!-- Display Recent Leave Requests -->
<div class="recent-leave-requests">
    <h3>Recent Leave Requests</h3>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Date Submitted</th> <!-- New Column for Date Submitted -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $recentLeaveRequests->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                    <td><?php 
                        // Format the date_submitted for better readability
                        echo date("F j, Y, g:i a", strtotime($row['date_submitted'])); 
                    ?></td> <!-- Display Date Submitted in Readable Format -->
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


    </div>
</main>


<style>

</style>



<script src="../js/no-previousbutton.js"></script>
<script src="../js/admin-sign_out.js"></script>
<script src="../js/toggle-darkmode.js"></script>

<footer>
            <p>HRMS3 Dashboard</p>
        </footer>
</body>
</html>
