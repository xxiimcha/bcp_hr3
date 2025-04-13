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

// Fetch employee data from external API
$employee_no = $_SESSION['employee_id'];
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$api_response = file_get_contents($api_url);
$api_data = json_decode($api_response, true);

$employee = null;
foreach ($api_data['data'] as $emp) {
    if ($emp['employee_no'] === $employee_no) {
        $employee = $emp;
        $employee['employee_name'] = $emp['firstname'] . ' ' . $emp['lastname'];
        break;
    }
}

if (!$employee) {
    die("Employee not found from API.");
}

// Optional: assign a dummy shift schedule if needed
$employee['shift_start'] = '08:00:00';
$employee['shift_end'] = '17:00:00';

// Fetch leave balances from local DB (use employee_no)
$leaveBalanceSql = "SELECT lt.leave_type, elb.balance
                    FROM employee_leave_balances elb
                    JOIN leave_types lt ON elb.leave_id = lt.leave_id
                    WHERE elb.employee_id = '$employee_no'";
$leaveBalanceResult = mysqli_query($conn, $leaveBalanceSql);

// Fetch attendance records
$attendanceSql = "SELECT a.attendance_date, a.time_in, a.time_out, a.overtime_in, a.overtime_out, a.status 
                  FROM attendance a 
                  WHERE a.employee_id = '$employee_no' 
                  ORDER BY a.attendance_date DESC";
$attendanceResult = mysqli_query($conn, $attendanceSql);

// Filtered attendance
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
// Fetch records from `employee_timesheet` using employee_no
$query = "SELECT * FROM employee_timesheet WHERE employee_id = '$employee_no'";
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND DATE(time_in) BETWEEN '$from_date' AND '$to_date'";
}
$attendanceResult = mysqli_query($conn, $query);

// Debug fallback if query fails
if (!$attendanceResult) {
    die("Error fetching timesheet data: " . mysqli_error($conn));
}

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



        <!-- Attendance Section -->
        <section id="attendance" class="section">
            
        <h4>Attendance Records</h4>
        <form method="GET" action="" class="filter-form">
    <label for="from_date" class="form-label">From:</label>
    <input type="date" id="from_date" name="from_date" class="form-input" value="<?php echo isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']) : ''; ?>" required>

    <label for="to_date" class="form-label">To:</label>
    <input type="date" id="to_date" name="to_date" class="form-input" value="<?php echo isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']) : ''; ?>" required>

    <button type="submit" class="submit-button">Filter</button>
</form>


            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Worked Hours</th>
                        <th>Overtime In</th>
                        <th>Overtime Out</th>
                        <th>Early Arrival Hours</th>
                        <th>Late Departure Hours</th>
                        <th>Total Overtime Hours</th>
                        <th>Total Worked Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
if ($attendanceResult->num_rows > 0) {
    while ($row = $attendanceResult->fetch_assoc()) {
        // Default shift start and end times from the employee's shift
        $shift_start = new DateTime($employee['shift_start']);
        $shift_end = new DateTime($employee['shift_end']);
        $total_shift_hours = $shift_start->diff($shift_end)->h + ($shift_start->diff($shift_end)->i / 60);

        // Initialize values to zero for 'Absent' status
        $worked_hours = 0;
        $early_arrival = 0;
        $late_departure = 0;
        $total_overtime = 0;
        $total_overtime_hours = 0;
        $total_worked_hours = 0;

        // Check if the status is 'Absent'
        if ($row['status'] == 'Absent') {
            $worked_hours = 0;
            $early_arrival = 0;
            $late_departure = 0;
            $total_overtime_hours = 0;
            $status = 'Absent'; // explicitly set the status to Absent
        } elseif ($row['status'] == 'Leave') {
            // Calculate worked hours based only on shift times if the status is 'Leave'
            $worked_hours = $total_shift_hours; // As the employee was on leave, we only consider the shift duration
            $early_arrival = 0;
            $late_departure = 0;
            $total_overtime_hours = 0;
            $status = 'Leave'; // explicitly set the status to Leave
        } elseif (!is_null($row['time_in']) && is_null($row['time_out'])) {
            // Regular calculation if time_in is present but time_out is not
            $worked_hours = $total_shift_hours / 2; // Adjust logic as needed
            $early_arrival = 0;
            $late_departure = 0;
            $total_overtime_hours = 0;
            $status = 'Present'; // Mark as Present if time_in is there but no time_out
        } elseif (!is_null($row['time_in']) && !is_null($row['time_out'])) {
            // Regular calculation if both time_in and time_out are available
            $time_in = new DateTime($row['time_in']);
            $time_out = new DateTime($row['time_out']);

            // Calculate hours worked only during the shift
            $shift_work_start = max($time_in, $shift_start);
            $shift_work_end = min($time_out, $shift_end);
            $worked_hours = $shift_work_start->diff($shift_work_end)->h + ($shift_work_start->diff($shift_work_end)->i / 60);

            // Calculate early arrival and late departure hours separately
            $early_arrival = ($time_in < $shift_start) ? $shift_start->diff($time_in)->h + ($shift_start->diff($time_in)->i / 60) : 0;
            $late_departure = ($time_out > $shift_end) ? $time_out->diff($shift_end)->h + ($time_out->diff($shift_end)->i / 60) : 0;

            $total_overtime_hours = $early_arrival + $late_departure;
            $status = 'Present'; // Mark as Present if time_in and time_out are available
        }

        // Check if overtime fields are set, and adjust the status to 'Present' for overtime cases
        if (!empty($row['overtime_in']) || !empty($row['overtime_out'])) {
            $status = 'Present'; // Mark as Present if there is overtime activity
        }

        // Total worked hours = worked hours + total overtime hours
        $total_worked_hours = $worked_hours + $total_overtime_hours;

        // Format the date in the desired "Month day, Year" format
        $attendance_date = new DateTime($row['attendance_date']);
        $formatted_date = $attendance_date->format('F j, Y'); // "November 13, 2024"

        // Format output
        echo '<tr>';
        echo '<td>' . htmlspecialchars($formatted_date) . '</td>';
        echo '<td>' . ($row['status'] == 'Absent' ? 'N/A' : htmlspecialchars($row['time_in'])) . '</td>';
        echo '<td>' . ($row['status'] == 'Absent' ? 'N/A' : htmlspecialchars($row['time_out'])) . '</td>';
        echo '<td>' . number_format($worked_hours, 2) . ' hours</td>';
        echo '<td>' . ($row['status'] == 'Absent' ? 'N/A' : ($row['overtime_in'] ? htmlspecialchars($row['overtime_in']) : 'N/A')) . '</td>';
        echo '<td>' . ($row['status'] == 'Absent' ? 'N/A' : ($row['overtime_out'] ? htmlspecialchars($row['overtime_out']) : 'N/A')) . '</td>';
        echo '<td>' . number_format($early_arrival, 2) . ' hours</td>';
        echo '<td>' . number_format($late_departure, 2) . ' hours</td>';
        echo '<td>' . number_format($total_overtime_hours, 2) . ' hours</td>';
        echo '<td>' . number_format($total_worked_hours, 2) . ' hours</td>';
        echo '<td>' . htmlspecialchars($status) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="11">No attendance records found.</td></tr>';
}
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
