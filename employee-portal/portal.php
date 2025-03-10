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

        <?php if (isset($_GET['message'])): ?>
            <div class="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>


            <!-- Dashboard Section -->
             <br>
        <section id="dashboard" class="section">
        <h3>Welcome to Your Dashboard</h3>
        <p>Quick links and recent updates will appear here.</p>
        <!-- Additional dashboard content could go here -->
        </section>


    </main>

    <style>

</style>
<script src="../js/portal-employee.js"></script>

</body>
</html>
