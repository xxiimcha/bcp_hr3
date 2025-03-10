<?php
include 'config.php'; // Assuming you have a database connection file

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="img/logo.webp">
    <title>Paradise Hotel</title>
    <link rel="stylesheet" href="css/maindashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->
    <div class="top-nav">
        <ul>
            <h1 class="logopos">Paradise <br> Hotel</h1><br>
            <li class="top">
                <a class="top1" href="maindashboard.php">Home</a>
                <div class="dropdown">
                    <div class="dropdown-column">
                        <h3>Payroll</h3>
                        <a href="admin/time-and-attendance-home.php">Time and Attendance</a>
                        <a href="Employee-information/employee-list.php">Employee Information</a>
                        <a href="payroll/log-in.php">Payroll Processing</a>
                    </div>           
                    <div class="dropdown-column">
                        <h3>Payroll Settings</h3>
                        <a href="tax-tables.html">Tax Tables</a>
                        <a href="overtime-rules.html">Overtime Rules</a>
                        <a href="payroll-deduction.html">Payroll Deductions</a>
                        <a href="direct-deposit-settings.html">Direct Deposit Settings</a>
                    </div>          
                    <div class="dropdown-column">
                        <h3>Charts and Graphs</h3>
                        <a href="#">Revenue Trend</a>
                        <a href="#">Profit Margin Trend</a>
                        <a href="#">Cash Flow Chart</a>
                        <a href="#">Financial Ratios Chart</a>
                    </div>

                    <div class="dropdown-column">
                        <h3>Quick Access Links</h3>
                        <a href="#">Frequently Used Reports</a>
                        <a href="#">Recent Transactions</a>
                        <a href="#">Quick Actions</a>
                    </div>            
                </div>
            </li>
            <li class="top">
                <a class="top1" href="homeversion2.html">Transactions</a>
                <div class="dropdown">
                    <div class="dropdown-column">
                        <h3>Point of Sale</h3>
                        <a href="#">Sales Module</a>
                        <a href="#">Inventory Management</a>
                        <a href="#">Customer Management</a>
                        <a href="#">Reporting and Analytics</a>
                        <a href="#">Integrations</a>
                    </div>
                    <div class="dropdown-column">
                        <h3>Account Recievable</h3>
                        <a href="#">Customer Master File</a>
                        <a href="#">Invoice Generation</a>
                        <a href="#">Aging Reports</a>
                        <a href="#">Collections Management</a>
                        <a href="#">Credit Memo Management</a>
                    </div>
                    <div class="dropdown-column">
                        <h3>Account Payable</h3>
                        <a href="#">Vendor Master File</a>
                        <a href="#">Purchase Order Processing</a>
                        <a href="#">Invoice Matching</a>
                        <a href="#">Disbursement Management</a>
                        <a href="#">Check Register</a>
                    </div>
                    <div class="dropdown-column">
                        <h3>Inventory</h3>
                        <a href="#">Item Master File</a>
                        <a href="#">Stock Transfers</a>
                        <a href="#">Physical Inventory</a>
                        <a href="#">Cost of Goods Sold Calculation</a>
                        <a href="#">Inventory Valuation</a>
                    </div>
                </div>
            </li>
            <li class = "top">
                <a class="top1" href="#settings">Settings</a>
                <div class="dropdown">
                    <div class="dropdown-column">
                        <h3>General Settings</h3>
                        <a href="#">Company Information</a>
                        <a href="#">Currency Settings</a>
                        <a href="#">Time Zone Settings</a>
                    </div>
                    <div class="dropdown-column">
                        <h3>User Management</h3>
                        <a href="#">User Roles</a>
                        <a href="admin-user-accounts.php">User Accounts</a>
                        <a href="#">Password Management</a>
                        <a href="#">User Permissions</a>
                    </div>
                    <div class="dropdown-column">
                        <h3>Chart of Accounts Settings</h3>
                        <a href="#">Account Structure</a>
                        <a href="#">Account Types</a>
                        <a href="#">Account Templates</a>
                    </div>
                    <div class="dropdown-column">
                        <h3>Inventory Settings</h3>
                        <a href="#">Inventory Valuation Methods</a>
                        <a href="#">Stock Levels:</a>
                        <a href="#">Reorder Points</a>
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
        <div class="dropdown-content">
            <a href="manage_account.php">Manage Account</a>
        </div>
        
    </div>
    
</div><button type="button" class="logout" id="logout-button">
    <i class="fas fa-sign-out-alt"></i>
    </button>
<!-- end of topnav -->
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


<script src="js/no-previousbutton.js"></script>
<script src="js/main-sign-out.js"></script>
<script src="js/toggle-darkmode.js"></script>


</body>
</html>
