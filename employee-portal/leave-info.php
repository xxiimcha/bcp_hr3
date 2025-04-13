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

// === 1. Fetch employee details from API ===
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
    echo "Employee not found in API.";
    exit();
}

// Escape employee_id since it's a user-controlled value
$employee_id_escaped = mysqli_real_escape_string($conn, $employee_id);

// === 2. Fetch leave balances from local DB ===
$leaveBalanceSql = "
    SELECT *
    FROM employee_leave_balances elb
    JOIN leave_types lt ON elb.leave_id = lt.leave_id
    WHERE elb.employee_id = '$employee_id_escaped'
";
$leaveBalanceResult = mysqli_query($conn, $leaveBalanceSql);

// === 3. Fetch leave records from local DB ===
$allRequestsSql = "
    SELECT lr.leave_id, lt.leave_type, lr.start_date, lr.end_date, lr.total_days, lr.status, lr.remarks 
    FROM employee_leave_records lr 
    JOIN leave_types lt ON lr.leave_id = lt.leave_id 
    WHERE lr.employee_id = '$employee_id_escaped'
";
$allRequestsResult = mysqli_query($conn, $allRequestsSql);

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
    <!-- Top Navigation Bar -->
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

        <!-- Apply Leave Section -->
        <section id="applyLeave" class="section">
            <h4>Leave Balance</h4>
            <table class="balance-table">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($leaveBalanceResult && mysqli_num_rows($leaveBalanceResult) > 0) {
                        while ($row = mysqli_fetch_assoc($leaveBalanceResult)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['leave_type']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['balance']) . ' days</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="2">No leave balances found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

                <!-- New Table for Detailed Leave Records -->
    <table class="leave-approved">
    <h4>Leave Records</h4>

        <thead>
            <tr>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch all leave requests for the logged-in employee
            $allRequestsSql = "SELECT lr.leave_id, lt.leave_type, lr.start_date, lr.end_date, lr.total_days, lr.status, lr.remarks 
                               FROM employee_leave_records lr 
                               JOIN leave_types lt ON lr.leave_id = lt.leave_id 
                               WHERE lr.employee_id = ?";
            $allRequestsStmt = $conn->prepare($allRequestsSql);
            $allRequestsStmt->bind_param("i", $employee_id);
            $allRequestsStmt->execute();
            $allRequestsResult = $allRequestsStmt->get_result();

            if ($allRequestsResult->num_rows > 0) {
                while ($row = $allRequestsResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['leave_type']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['start_date']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['end_date']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['total_days']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['remarks']) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="7">No leave requests found.</td></tr>';
            }

            $allRequestsStmt->close();
            ?>
        </tbody>
    </table>
        </section>

    </main>

    <style>

</style>

<script src="../js/portal-employee.js"></script>
</body>
</html>
