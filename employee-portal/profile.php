<?php
include '../config.php';
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// === STEP 1: Fetch employee details from external API ===
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$api_response = file_get_contents($api_url);
$api_data = json_decode($api_response, true);

$employee = null;
foreach ($api_data['data'] as $emp) {
    if ($emp['employee_no'] == $employee_id) {
        $employee = $emp;
        $employee['employee_name'] = $emp['firstname'] . ' ' . $emp['lastname'];
        break;
    }
}

if (!$employee) {
    echo "Employee not found from API.";
    exit();
}

// === STEP 2: Fetch shift schedule from DB ===
$shiftQuery = "SELECT st.shift_start, st.shift_end 
               FROM emp_shifts es 
               JOIN shift_types st ON es.shift_type_id = st.shift_type_id 
               WHERE es.employee_id = $employee_id";
$shiftResult = mysqli_query($conn, $shiftQuery);
if ($shiftResult && mysqli_num_rows($shiftResult) > 0) {
    $shift = mysqli_fetch_assoc($shiftResult);
    $employee['shift_start'] = $shift['shift_start'];
    $employee['shift_end'] = $shift['shift_end'];
} else {
    $employee['shift_start'] = 'N/A';
    $employee['shift_end'] = 'N/A';
}

// === STEP 3: Fetch leave balances ===
$leaveBalanceSql = "SELECT lt.leave_type, elb.balance
                    FROM employee_leave_balances elb
                    JOIN leave_types lt ON elb.leave_code = lt.leave_code
                    WHERE elb.employee_id = $employee_id";
$leaveBalanceResult = mysqli_query($conn, $leaveBalanceSql);

// === STEP 4: Fetch attendance ===
$attendanceSql = "SELECT a.attendance_date, a.time_in, a.time_out, a.overtime_in, a.overtime_out, a.status 
                  FROM attendance a 
                  WHERE a.employee_id = $employee_id 
                  ORDER BY a.attendance_date DESC";
$attendanceResult = mysqli_query($conn, $attendanceSql);

// === STEP 5: Attendance filter by date ===
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

$filterQuery = "SELECT * FROM attendance WHERE employee_id = $employee_id";
if (!empty($from_date) && !empty($to_date)) {
    $filterQuery .= " AND attendance_date BETWEEN '$from_date' AND '$to_date'";
}
$attendanceResult = mysqli_query($conn, $filterQuery);
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
        <p><strong>Employee No:</strong> <?php echo htmlspecialchars($employee['employee_no']); ?></p>
        <p><strong>Employee Name:</strong> <?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars(date('F d, Y', strtotime($employee['birthdate']))); ?></p>
        <p><strong>Contact No:</strong> <?php echo htmlspecialchars($employee['number']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($employee['address']); ?></p>
    </div>

    <!-- Right Panel -->
    <div class="profile-panel">
        <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['position']); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($employee['gender']); ?></p>
        <p><strong>Civil Status:</strong> <?php echo htmlspecialchars($employee['civil_status']); ?></p>
        <p><strong>Shift Time:</strong> <?php echo $employee['shift_start'] . ' - ' . $employee['shift_end']; ?></p>
        <p><strong>Profile Picture:</strong><br>
            <img src="<?php echo htmlspecialchars($employee['profile']); ?>" alt="Profile Picture" style="width:100px; height:100px; border-radius:50%; object-fit:cover; margin-top:10px;">
        </p>
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
