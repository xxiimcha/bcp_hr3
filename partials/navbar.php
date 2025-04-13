


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Timesheet</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/employee-list.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- Include jQuery and QR Code Library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>


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
                        <a href="../admin/time-and-attendance-home.php">
                            <i class="fas fa-clock"></i> Time and Attendance <!-- Icon for Time and Attendance -->
                        </a>
                        <a href="../employee-information/employee-list.php">
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



