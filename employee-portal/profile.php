<?php
include '../config.php';
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch employee details along with their department name from the database
$employee_id = $_SESSION['employee_id'];
$sql = "SELECT e.*, d.department_name, st.shift_start, st.shift_end 
        FROM employee_info e 
        JOIN departments d ON e.department_id = d.department_id 
        JOIN employee_shifts es ON e.employee_id = es.employee_id 
        JOIN shift_types st ON es.shift_type_id = st.shift_type_id
        WHERE e.employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
} else {
    echo "No employee found.";
    exit();
}
// Fetch leave balances for the logged-in employee
$leaveBalanceSql = "SELECT lt.leave_type, elb.balance
                    FROM employee_leave_balances elb
                    JOIN leave_types lt ON elb.leave_code = lt.leave_code
                    WHERE elb.employee_id = ?";
$leaveBalanceStmt = $conn->prepare($leaveBalanceSql);
$leaveBalanceStmt->bind_param("i", $employee_id);
$leaveBalanceStmt->execute();
$leaveBalanceResult = $leaveBalanceStmt->get_result();




// Fetch attendance records for the logged-in employee
$attendanceSql = "SELECT a.attendance_date, a.time_in, a.time_out, a.overtime_in, a.overtime_out, a.status 
                  FROM attendance a 
                  WHERE a.employee_id = ? 
                  ORDER BY a.attendance_date DESC";

$attendanceStmt = $conn->prepare($attendanceSql);
$attendanceStmt->bind_param("i", $employee_id);
$attendanceStmt->execute();
$attendanceResult = $attendanceStmt->get_result();



// Initialize date variables
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Define the base query
$query = "SELECT * FROM attendance WHERE employee_id = ?";

// Add date range filtering if both dates are specified
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND attendance_date BETWEEN ? AND ?";
}

// Prepare and execute the query with the date range parameters
$stmt = $conn->prepare($query);
if (!empty($from_date) && !empty($to_date)) {
    $stmt->bind_param("iss", $employee_id, $from_date, $to_date);
} else {
    $stmt->bind_param("i", $employee_id);
}
$stmt->execute();
$attendanceResult = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <title>Employee Portal</title>
    <link rel="stylesheet" href="../css/employeePortal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <script>


    </script>
<header class="top-nav">
    <div class="logo-section">
        <button onclick="toggleSidebar()" class="sidebar-toggle-button" style="margin-right: 10px;">
            <i class="fas fa-bars"></i> <!-- Font Awesome icon for a menu -->
        </button>
        <img src="../img/logo.webp" alt="Paradise Hotel Logo" class="top-logo">
        <span>Paradise Hotel Employee Portal</span>
    </div>

    <div class="user-info">
                <!-- Dark Mode Toggle Button -->
                <button onclick="toggleDarkMode()" class="dark-mode-toggle" >
            <i id="dark-mode-icon" class="fas"></i> <!-- Icon will change dynamically -->
        </button>

        <span>Welcome, <?php echo htmlspecialchars($employee['employee_name']); ?></span>
        

        <!-- Logout Button -->
        <button onclick="showLogoutOverlay()" class="logout">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>
</header>



<!-- Sidebar Navigation -->
<aside class="sidebar">
    <ul>
        <li><a href="portal.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        <li><a href="attendance-record.php"><i class="fas fa-calendar-check"></i> Attendance Record</a></li>
        <li><a href="leave-info.php"><i class="fas fa-clipboard-list"></i> Leave Balance</a></li>
        <li><a href="leave-request.php"><i class="fas fa-envelope-open-text"></i> Leave Requests</a></li>
    </ul>
</aside>
<style>/* Sidebar List */

</style>
    <!-- Main Content Area -->
    <main class="content">

        <!-- Logout Overlay -->
        <div id="logoutOverlay" class="overlay" style="display: none;">
            <div class="overlay-content">
                <h2>Log Out Confirmation</h2>
                <p>Are you sure you want to log out?</p>
                <button onclick="confirmLogout()">Yes, Log Out</button>
                <button onclick="closeOverlay()">Cancel</button>
            </div>
        </div>


<!-- Profile Section -->
<section id="profile" class="section">
    <h3>Profile</h3>

    <!-- Left Panel -->
    <div class="profile-panel">
        <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['employee_id']); ?></p>
        <p><strong>Employee Name:</strong> <?php echo htmlspecialchars($employee['employee_name']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($employee['date_of_birth']); ?></p>
        <p><strong>Contact No:</strong> <?php echo htmlspecialchars($employee['contact_no']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email_address']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($employee['address']); ?></p>
    </div>

    <!-- Right Panel -->
    <div class="profile-panel">
        <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department_name']); ?></p>
        <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
        <p><strong>Date Hired:</strong> <?php echo htmlspecialchars($employee['date_hired']); ?></p>
    </div>
</section>
<?php if (isset($_GET['message'])): ?>
            <div class="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
<!-- Security Section -->
<section id="security" class="section">
    
    <h3>Security <br><br><hr></h3>
    <h5>Change your Password <br>Note: <i>All fields are required</i></h5>



    <!-- Password Change Form -->
    <form action="change-password.php" method="POST" id="change-password-form">
        <div>
            <label for="current-password">Current Password *</label>
            <input type="password" name="current_password" id="current-password" required>
        </div>
        <div>
            <label for="new-password">New Password *</label>
            <input type="password" name="new_password" id="new-password" required>
            <small>Minimum 8 characters, at least one number, one lowercase letter, one uppercase letter, and one non-alphanumeric character</small>
        </div>
        <div>
            <label for="confirm-password">Confirm Password *</label>
            <input type="password" name="confirm_password" id="confirm-password" required>
        </div>
        <div>
            <button type="submit" name="change_password">Change Password</button>
        </div>
    </form>
</section>






    </main>

    <style>
/* Profile Section Styling */
#profile {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
    background-color: #e7efec;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    margin: 20px;
}

#profile h3 {
    width: 100%;
    color: #35B535;
    margin-bottom: 10px;
    font-size: 24px;
}

/* Panel Styling */
.profile-panel {
    flex: 1 1 45%; /* Two-column layout */
    padding: 15px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

/* Individual Item Styling */
.profile-panel p {
    margin: 10px 0;
    font-size: 16px;
    color: #555;
}

.profile-panel p strong {
    color: #333;
    font-weight: 600;
}

/* Dark Mode Styling */
body.dark-mode #profile {
    background-color: #333;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.4);
}

body.dark-mode #profile h3 {
    color: #35B535;
}

body.dark-mode .profile-panel {
    background-color: #444;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.3);
}

body.dark-mode .profile-panel p {
    color: #ccc;
}

body.dark-mode .profile-panel p strong {
    color: #fff;
}

/* For responsiveness */
@media (max-width: 768px) {
    .profile-panel {
        flex: 1 1 100%;
    }
}
/* Change Password Section */
#security {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
    background-color: #e7efec;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    margin: 20px;

}

/* Title Styling */
#security h3 {
    width: 100%;
    color: #35B535;
    margin-bottom: 20px;
    font-size: 24px;
}

/* Change Password Form Styling */
#change-password-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    width: 100%;
}

#change-password-form div {
    display: flex;
    flex-direction: column;
    flex: 1 1 100%; /* Ensure each div element within the form takes up full width */
}

#change-password-form label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}


body.dark-mode #change-password-form label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #ddd;
}

#change-password-form input {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.3s ease;
}

#change-password-form input:focus {
    border-color: #35B535;
}

#change-password-form small {
    font-size: 12px;
    color: #888;
    margin-top: 5px;
}

/* Submit Button Styling */
#change-password-form button {
    background-color: #35B535;
    color: white;
    padding: 12px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#change-password-form button:hover {
    background-color: #2e9e2e;
}

/* Message Styles for Error and Success */
.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    border: 1px solid #f5c6cb;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    border: 1px solid #c3e6cb;
}

/* Dark Mode Styling for the Change Password Section */
body.dark-mode #security {
    background-color: #333;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.4);
}

body.dark-mode #security h3 {
    color: #35B535;
}
body.dark-mode #security h5 {
    color: #999;
}

body.dark-mode #change-password-form input {
    background-color: #444;
    color: white;
    border: 1px solid #555;
}

body.dark-mode #change-password-form input:focus {
    border-color: #35B535;
}

body.dark-mode #change-password-form button {
    background-color: #2e9e2e;
}

body.dark-mode .error-message {
    background-color: #f8d7da;
    color: #721c24;
}

body.dark-mode .success-message {
    background-color: #d4edda;
    color: #155724;
}

/* For responsiveness */
@media (max-width: 768px) {
    #security {
        width: 100%;
        padding: 15px;
    }

    #change-password-form {
        width: 100%;
    }

    /* Ensure form items take full width on small screens */
    #change-password-form div {
        flex: 1 1 100%;
    }
}


</style>

<script src="../js/portal-employee.js"></script>
</body>
</html>
