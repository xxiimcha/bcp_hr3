<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Load employee data from API
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$employee_data = [];

$response = file_get_contents($api_url);
if ($response !== false) {
    $decoded = json_decode($response, true);
    if (isset($decoded['data'])) {
        $employee_data = $decoded['data'];
    }
}

// Fetch all login records
$login_sql = "SELECT * FROM employee_logins";
$login_result = $conn->query($login_sql);
$login_data = [];
if ($login_result->num_rows > 0) {
    while ($row = $login_result->fetch_assoc()) {
        $login_data[$row['employee_id']] = $row; // employee_id here refers to employee_no
    }
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $employee_no = $_POST['employee_id'];
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $update_sql = "UPDATE employee_logins SET password = '$hashed_password' WHERE employee_id = '$employee_no'";
    if (mysqli_query($conn, $update_sql)) {
        echo "<p>Password successfully reset for employee ID: " . htmlspecialchars($employee_no) . "</p>";
    } else {
        echo "<p>Error resetting password: " . mysqli_error($conn) . "</p>";
    }
}

// Handle deletion
if (isset($_POST['delete_employee'])) {
    $employee_no = $_POST['employee_id'];
    $delete_sql = "DELETE FROM employee_logins WHERE employee_id = '$employee_no'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<p>Employee account successfully deleted for ID: " . htmlspecialchars($employee_no) . "</p>";
    } else {
        echo "<p>Error deleting employee: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <title>Employee Accounts and Password Reset</title>
    <link rel="stylesheet" href="../css/employee_accounts.css">
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
                        <a href="../admin/time-and-attendance-home.php">
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



    <h1>Employee Accounts</h1>
<a href="add_employee.php" class="add-employee-btn">Add Employee Account</a>

<table>
    <thead>
        <tr>
            <th>Employee Name</th>
            <th>Position</th>
            <th>Email</th>
            <th>Status</th>
            <th>Password (Hashed)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($employee_data as $emp) {
            $emp_no = $emp['employee_no'];
            if (isset($login_data[$emp_no])) {
                $login = $login_data[$emp_no];
                echo "<tr>";
                echo "<td>" . htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']) . "</td>";
                echo "<td>" . htmlspecialchars($emp['position']) . "</td>";
                echo "<td>" . htmlspecialchars($emp['email']) . "</td>";
                echo "<td>" . htmlspecialchars($emp['status']) . "</td>";
                echo "<td>" . htmlspecialchars($login['password']) . "</td>";
                echo "<td>
                        <button class='update-password-btn' onclick='showOverlay(\"$emp_no\")'>Update</button>
                        <button class='delete-employee-btn' onclick='showDeleteOverlay(\"$emp_no\", \"" . htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']) . "\")'>Delete</button>
                      </td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>
<!-- Password Update Overlay -->
<div id="pass-overlay" class="pass-overlay" onclick="hideOverlay()">
    <div class="pass-overlay-content" onclick="event.stopPropagation();">
        <h2>Update Password</h2>
        <form method="POST" onsubmit="return submitForm()">
            <input type="hidden" name="employee_id" id="employee_id">
            <input type="password" name="new_password" placeholder="New Password" required>
            <p style="color: red; font-size: 14px;">* Password must contain:</p>
            <ul style="list-style-type: disc; margin-left: 20px; color: #555;">
                <li>At least one uppercase letter</li>
                <li>At least one lowercase letter</li>
                <li>At least one number</li>
                <li>At least one special character (e.g., @, #, $, etc.)</li>
                <li>Minimum 8 characters</li>
            </ul>
            <button type="submit" class="reset_password" name="reset_password">Reset Password</button>
            <button type="button" class="cancel-btn"onclick="hideOverlay()">Cancel</button>
            <p id="password_error" class="error"></p>
        </form>
    </div>
</div>
<style></style>

<!-- Delete Employee Confirmation Overlay -->
<div id="delete-overlay" class="pass-overlay" onclick="hideOverlay()">
    <div class="delete-overlay-content" onclick="event.stopPropagation();">
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete the account of <br><strong id="delete_employee_name"></strong>?</p>
        <form method="POST">
            <input type="hidden" name="employee_id" id="delete_employee_id">
            <button class="delete-employee-btn"name="delete_employee">Delete</button>
            <button type="button" class="cancel-del-btn" onclick="hideOverlay()">Cancel</button>
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
        <p>2024 Employee Account</p>
    </footer>


</body>
<script src="../js/sign_out.js"></script>
<script src="../jsno-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
<script>
        function showOverlay(employeeId) {
            document.getElementById('pass-overlay').style.display = 'flex';
            document.getElementById('employee_id').value = employeeId;
            document.getElementById('password_error').innerText = ''; // Clear previous error message
        }

        function hideOverlay() {
            document.getElementById('pass-overlay').style.display = 'none';
            document.getElementById('delete-overlay').style.display = 'none';
        }

        function showDeleteOverlay(employeeId, employeeName) {
            document.getElementById('delete-overlay').style.display = 'flex';
            document.getElementById('delete_employee_id').value = employeeId;
            document.getElementById('delete_employee_name').innerText = employeeName;
        }

        function validatePassword(password) {
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            return passwordPattern.test(password);
        }

        function submitForm() {
            const passwordInput = document.querySelector('input[name="new_password"]');
            const passwordError = document.getElementById('password_error');

            if (!validatePassword(passwordInput.value)) {
                passwordError.innerText = 'Password must be at least 8 characters long, and include at least one uppercase letter, one lowercase letter, one number, and one special character.';
                return false; // Prevent form submission
            }

            return true; // Allow form submission
        }
    </script>    <?php
$conn->close();
?>
</html>


