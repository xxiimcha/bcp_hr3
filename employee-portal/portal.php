<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
include '../config.php';
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// === STEP 1: Fetch Employee Info from API ===
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$api_response = file_get_contents($api_url);
$api_data = json_decode($api_response, true);

$employee = null;
foreach ($api_data['data'] as $emp) {
    if ($emp['employee_no'] == $employee_id) {
        $employee = $emp;
        break;
    }
}

if (!$employee) {
    echo "Employee not found from API.";
    exit();
}

// === STEP 2: Fetch Shift Schedule from Local DB ===
$shiftResult = mysqli_query($conn, "SELECT st.shift_start, st.shift_end 
    FROM emp_shifts es 
    JOIN shift_types st ON es.shift_type_id = st.shift_type_id 
    WHERE es.employee_id = $employee_id");

if ($shiftResult && mysqli_num_rows($shiftResult) > 0) {
    $shiftData = mysqli_fetch_assoc($shiftResult);
    $employee['shift_start'] = $shiftData['shift_start'];
    $employee['shift_end'] = $shiftData['shift_end'];
} else {
    $employee['shift_start'] = 'N/A';
    $employee['shift_end'] = 'N/A';
}

// === STEP 3: Fetch Leave Balances ===
$leaveBalanceResult = mysqli_query($conn, "
    SELECT lt.leave_type, elb.balance
    FROM employee_leave_balances elb
    JOIN leave_types lt ON elb.leave_code = lt.leave_code
    WHERE elb.employee_id = $employee_id
");

// === STEP 4: Fetch Attendance Records (latest first) ===
$attendanceResult = mysqli_query($conn, "
    SELECT a.attendance_date, a.time_in, a.time_out, a.overtime_in, a.overtime_out, a.status 
    FROM attendance a 
    WHERE a.employee_id = $employee_id 
    ORDER BY a.attendance_date DESC
");

// === STEP 5: Date Range Filter (if any) ===
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

$query = "SELECT * FROM attendance WHERE employee_id = $employee_id";
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND attendance_date BETWEEN '$from_date' AND '$to_date'";
}
$attendanceResult = mysqli_query($conn, $query);
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
        <span>Welcome, <?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?></span>
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
