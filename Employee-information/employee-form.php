<?php
include '../config.php'; // Ensure the path is correct
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch existing departments from the database
$departments = [];
$sql = "SELECT department_id, department_name FROM departments";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = intval($_POST['employee-id']); // Ensure this is an integer
    $first_name = htmlspecialchars($_POST['first-name']);
    $middle_name = htmlspecialchars($_POST['middle-name']);
    $last_name = htmlspecialchars($_POST['last-name']);
    $employee_name = trim("$first_name $middle_name $last_name"); // Combine names
    $department_id = intval($_POST['department_id']); // Get department_id as an integer
    $job_title = htmlspecialchars($_POST['job-title']);
    $dob = $_POST['dob'];
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $date_hire = $_POST['date-hire'];
    $employment_status = htmlspecialchars($_POST['employment-status']);

    // Insert into the employee_info table
    $sql = "INSERT INTO employee_info (employee_id, employee_name, department_id, position, date_of_birth, contact_no, email_address, address, date_hired, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssss", $employee_id, $employee_name, $department_id, $job_title, $dob, $phone, $email, $address, $date_hire, $employment_status);

    if ($stmt->execute()) {
        echo "<script>alert('Employee information submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <title>Create Employee Form</title>
    <link rel="stylesheet" href="../css/employee_form.css">
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
    <br>
    <div class="content">
        <h2>Employee Information Form</h2>
        <form id="employee-info-form" method="post">
            <label for="employee-id">Employee ID:</label>
            <input type="number" id="employee-id" name="employee-id" required><br><br>

            <label for="first-name">First Name:</label>
            <input type="text" id="first-name" name="first-name" required><br><br>

            <label for="middle-name">Middle Name:</label>
            <input type="text" id="middle-name" name="middle-name"><br><br>

            <label for="last-name">Last Name:</label>
            <input type="text" id="last-name" name="last-name" required><br><br>

            <label for="department">Department:</label>
            <select id="department" name="department_id" required>
                <option value="" disabled selected>Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo htmlspecialchars($department['department_id']); ?>">
                        <?php echo htmlspecialchars($department['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="job-title">Job Title/Position:</label>
            <input type="text" id="job-title" name="job-title" required><br><br>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required><br><br>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required><br><br>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="address">Address:</label>
            <textarea id="address" name="address" rows="4" required></textarea><br><br>

            <label for="date-hire">Date of Hire:</label>
            <input type="date" id="date-hire" name="date-hire" required><br><br>

            <label for="employment-status">Employment Status:</label>
            <select id="employment-status" name="employment-status" required>
                <option value="" disabled selected>Select Employment Status</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contractual">Contractual</option>
            </select><br><br>

            <button type="submit">Submit Employee Information</button>
        </form>
    </div>

    <footer>
        <p>2024 Employee Information</p>
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
    
</body>
<script src="../js/sign_out.js"></script>
<script src="../jsno-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
</html>
