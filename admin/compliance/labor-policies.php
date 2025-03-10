<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../../img/logo.webp">
    <title>Violations</title>
    <link rel="stylesheet" href="../../css/labor_policies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->
<div class="top-nav">
    <ul>
        <a href="../../maindashboard.php">
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
                    <a href="../time-and-attendance-home.php">
                        <i class="fas fa-clock"></i> Time and Attendance <!-- Icon for Time and Attendance -->
                    </a>
                    <a href="../../Employee-information/employee-list.php">
                        <i class="fas fa-users"></i> Employee Information <!-- Icon for Employee Information -->
                    </a>
                    <a href="payroll/log-in.php">
                        <i class="fas fa-calculator"></i> Payroll Processing <!-- Icon for Payroll Processing -->
                    </a>
                </div>           
            </div>
        </li>
        <li class="top">
            <a class="top1" href="../time-and-attendance-home.php">
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
                    <a href="../clocking-system.php">
                        <i class="fas fa-clock"></i> Clocking System <!-- Icon for Clocking System -->
                    </a>
                    <a href="../timesheet.php">
                        <i class="fas fa-calendar-alt"></i> Daily Record <!-- Icon for Daily Record -->
                    </a>
                    <a href="../attendance-summary.php">
                        <i class="fas fa-list"></i> Attendance Summary <!-- Icon for Attendance Summary -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Leave Management</b></h3> <!-- Icon for Leave Management -->
                    <a href="../leavemanagement.php">
                        <i class="fas fa-envelope-open-text"></i> Leave Requests <!-- Icon for Leave Requests -->
                    </a>
                    <a href="../leave-record.php">
                        <i class="fas fa-file-alt"></i> Employee Leave Records <!-- Icon for Leave Records -->
                    </a>
                    <a href="../leave-type-list.php">
                        <i class="fas fa-list-alt"></i> List of Leave Types <!-- Icon for Leave Types -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Shift Management</b></h3> <!-- Icon for Shift Management -->
                    <a href="../manage-shift.php">
                        <i class="fas fa-calendar"></i> Manage Shift <!-- Icon for Manage Shift -->
                    </a>
                    <a href="../shift-types.php">
                        <i class="fas fa-layer-group"></i> Shift Types <!-- Icon for Shift Types -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Compliance & Labor Law Adherence</b></h3> <!-- Icon for Compliance -->
                    <a href="violations.php">
                        <i class="fas fa-exclamation-triangle"></i> Violations <!-- Icon for Violations -->
                    </a>
                    <a href="compliance-report.php">
                        <i class="fas fa-file-contract"></i> Compliance Report <!-- Icon for Compliance Report -->
                    </a>
                    <a href="labor-policies.php">
                        <i class="fas fa-book"></i> Labor Policies <!-- Icon for Labor Policies -->
                    </a>
                    <a href="adherence-monitoring.php">
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
            <div class="top1dropdown">
                <div class="dropdown-column">
                    <h3><i class="fas fa-cogs"></i> General Settings</h3>
                    <a href="#"><i class="fas fa-building"></i> Company Information</a>
                    <a href="#"><i class="fas fa-coins"></i> Currency Settings</a>
                    <a href="#"><i class="fas fa-globe"></i> Time Zone Settings</a>
                </div>
                <div class="dropdown-column">
                    <h3><i class="fas fa-user-shield"></i> User Management</h3>
                    <a href="#"><i class="fas fa-user-tag"></i> User Roles</a>
                    <a href="admin-user-accounts.php"><i class="fas fa-users-cog"></i> User Accounts</a>
                    <a href="#"><i class="fas fa-key"></i> Password Management</a>
                    <a href="#"><i class="fas fa-lock"></i> User Permissions</a>
                </div>
                <div class="dropdown-column">
                    <h3><i class="fas fa-file-invoice"></i> Chart of Accounts Settings</h3>
                    <a href="#"><i class="fas fa-sitemap"></i> Account Structure</a>
                    <a href="#"><i class="fas fa-layer-group"></i> Account Types</a>
                    <a href="#"><i class="fas fa-th"></i> Account Templates</a>
                </div>
                <div class="dropdown-column">
                    <h3><i class="fas fa-boxes"></i> Inventory Settings</h3>
                    <a href="#"><i class="fas fa-balance-scale"></i> Inventory Valuation Methods</a>
                    <a href="#"><i class="fas fa-box"></i> Stock Levels</a>
                    <a href="#"><i class="fas fa-clipboard-list"></i> Reorder Points</a>
                </div>
            </div>
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
                <a href="../../manage_account.php">Manage Account</a>
            </div>
        </div>
    </div>
    <button type="button" class="logout" id="logout-button" style="margin-right: 10px;">
        <i class="fas fa-sign-out-alt"></i> <!-- Icon for logout -->
    </button>
</div>
<!-- END OF TOP NAV BAR -->

<!-- Labor Policy table -->
<div class="table-container">
            <h2>Labor Policies</h2><br>
            <table>
                <thead>
                    <tr>
                        <th>Policy Name</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Upcoming Changes</th>
                    </tr>
                </thead>
                <tbody id="laborPoliciesTableBody">
                </tbody>
            <tbody>
                <tr>
                    <td>Attendance Policy</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>July 15, 2023</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Overtime Rules</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>September 1, 2023</td>
                    <td><span class="upcoming-changes"><i class="fas fa-exclamation-circle"></i> Changes pending from 2024 Labor Law</span></td>
                </tr>
                <tr>
                    <td>Leave Policy</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>January 10, 2022</td>
                    <td><span class="upcoming-changes"><i class="fas fa-exclamation-circle"></i> Major updates coming by Q4 2024</span></td>
                </tr>
                <tr>
                    <td>Health and Safety Regulations</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>March 25, 2023</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Anti-Harassment Policy</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>June 12, 2023</td>
                    <td><span class="upcoming-changes"><i class="fas fa-exclamation-circle"></i> New training requirements in 2024</span></td>
                </tr>
            </tbody>
        </table>
    </div>

<footer>
    <p>2024 Labor Policies</p>
</footer>

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


<script src="../../js/no-previousbutton.js"></script>
<script src="../../js/toggle-darkmode.js"></script>

</body>
<script>// main-sign-out.js
document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.getElementById('logout-button');
    const dialogOverlay = document.getElementById('dialog-overlay');
    const confirmButton = document.getElementById('confirm-button');
    const cancelButton = document.getElementById('cancel-button');

    // Show dialog on logout button click
    logoutButton.addEventListener('click', function () {
        dialogOverlay.style.display = 'flex'; // Display the dialog
    });

    // Handle confirm button click
    confirmButton.addEventListener('click', function () {
        // Logic for signing out (e.g., redirecting to a logout page)
        window.location.href = '../../log-out2.php'; // Example logout redirect
    });

    // Handle cancel button click
    cancelButton.addEventListener('click', function () {
        dialogOverlay.style.display = 'none'; // Hide the dialog
    });
});
</script>
</html>
