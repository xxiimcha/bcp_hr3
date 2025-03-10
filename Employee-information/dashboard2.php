<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <title>Home - Employee Information</title>
    <link rel="stylesheet" href="../css/employee-dashboard.css">
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
                <i class="fas fa-compass"></i> <!-- Icon for Compass -->
                    Navigate
                </a>
                <div class="top1dropdown">
                    <div class="dropdown-column">
                        <h3>Payroll</h3>
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
                <a class="top1" href="">
                    <i class="fas fa-user-cog"></i> <!-- Icon for Manage Employee -->
                    Manage Employee
                </a>
                <div class="top1dropdown">
                    <div class="dropdown-column">
                        <h3><b>Manage Employee</b></h3>
                        <a href="employee-form.php">
                            <i class="fas fa-user-plus"></i> Employee Form <!-- Icon for Employee Form -->
                        </a>
                        <a href="employee-list.php">
                            <i class="fas fa-list-ul"></i> Employee List <!-- Icon for Employee List -->
                        </a>
                    </div>
                    <div class="dropdown-column">
                        <h3><b>Employee Portal</b></h3>
                        <a href="employee_accounts.php">
                            <i class="fas fa-id-badge"></i> Employee Accounts <!-- Icon for Employee Accounts -->
                        </a>
                    </div>
                    <div class="dropdown-column">
                        <h3><b>Department</b></h3>
                        <a href="department.php">
                            <i class="fas fa-building"></i> Manage Department <!-- Icon for Manage Department -->
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
                        <h3>General Settings</h3>
                        <a href="#">
                            <i class="fas fa-info-circle"></i> Company Information <!-- Icon for Company Information -->
                        </a>
                        <a href="#">
                            <i class="fas fa-money-bill-wave"></i> Currency Settings <!-- Icon for Currency Settings -->
                        </a>
                        <a href="#">
                            <i class="fas fa-clock"></i> Time Zone Settings <!-- Icon for Time Zone Settings -->
                        </a>
                    </div>
                    <div class="dropdown-column">
                        <h3>User Management</h3>
                        <a href="#">
                            <i class="fas fa-user-shield"></i> User Roles <!-- Icon for User Roles -->
                        </a>
                        <a href="admin-user-accounts.php">
                            <i class="fas fa-user-friends"></i> User Accounts <!-- Icon for User Accounts -->
                        </a>
                        <a href="#">
                            <i class="fas fa-lock"></i> Password Management <!-- Icon for Password Management -->
                        </a>
                        <a href="#">
                            <i class="fas fa-user-lock"></i> User Permissions <!-- Icon for User Permissions -->
                        </a>
                    </div>
                    <div class="dropdown-column">
                        <h3>Chart of Accounts Settings</h3>
                        <a href="#">
                            <i class="fas fa-list-alt"></i> Account Structure <!-- Icon for Account Structure -->
                        </a>
                        <a href="#">
                            <i class="fas fa-tags"></i> Account Types <!-- Icon for Account Types -->
                        </a>
                        <a href="#">
                            <i class="fas fa-file-invoice"></i> Account Templates <!-- Icon for Account Templates -->
                        </a>
                    </div>
                    <div class="dropdown-column">
                        <h3>Inventory Settings</h3>
                        <a href="#">
                            <i class="fas fa-box"></i> Inventory Valuation Methods <!-- Icon for Inventory Valuation -->
                        </a>
                        <a href="#">
                            <i class="fas fa-warehouse"></i> Stock Levels <!-- Icon for Stock Levels -->
                        </a>
                        <a href="#">
                            <i class="fas fa-arrow-alt-circle-up"></i> Reorder Points <!-- Icon for Reorder Points -->
                        </a>
                    </div>
                </div>
            </li>
        </ul>
         <!-- <button type="button" id="darkModeToggle" class="dark-mode-toggle">Dark Mode</button> -->
         <button type="button" id="darkModeToggle" class="dark-mode-toggle" aria-label="Toggle Dark Mode">
            <i class="fas fa-moon"></i> <!-- Example icon for dark mode -->
         </button>

        <!-- USER  -->
        <div class="admin-section">
            <div class="admin-name">
                User - <?php echo htmlspecialchars($username); ?>
                <div class="admin-dropdown-content">
                    <a href="../manage_account.php">
                    Manage Account <!-- Icon for Manage Account -->
                    </a>
                </div>
            </div>
        </div>
        <button type="button" class="logout" id="logout-button">
            <i class="fas fa-sign-out-alt"></i> <!-- Icon for Logout -->
        </button>
        <!-- END OF TOP NAVIGATIONAL BAR -->
    </div>
<div class="container">
    <div class="dashboard-panel">
        <h2>Employee Dashboard</h2><br>
        
        <div class="summary-section">
            <h3>SECTION 1</h3>
            
        </div>

        <div class="summary-section">
            <h3>SECTION 2</h3>
            <ul>

            </ul>
        </div>
    </div>
</div>

<footer>
    <p>2024 Time and Attendance Dashboard</p>
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

<script src="../js/main-sign-out.js"></script>
<script src="../jsno-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
</body>

<style>
</style>

</html>
