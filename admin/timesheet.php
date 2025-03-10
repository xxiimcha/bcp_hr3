<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch departments
$departments = [];
$dept_sql = "SELECT department_id, department_name FROM departments";
$dept_result = $conn->query($dept_sql);
if ($dept_result->num_rows > 0) {
    while ($row = $dept_result->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Fetch shift types
$shift_types = [];
$shift_sql = "SELECT shift_type_id, shift_name FROM shift_types";
$shift_result = $conn->query($shift_sql);
if ($shift_result->num_rows > 0) {
    while ($row = $shift_result->fetch_assoc()) {
        $shift_types[] = $row;
    }
}

// Initialize variables for filtering attendance
$attendance_records = [];
$selected_department = $_GET['department'] ?? 'all-departments';
$selected_shift = $_GET['shift'] ?? 'all-shifts';

// Today's date for attendance filtering
$today_date = date('Y-m-d');

// Build SQL query to fetch today's attendance records
$sql = "SELECT a.attendance_id, a.employee_id, ei.employee_name, d.department_name, ei.position, 
a.attendance_date, a.status, a.time_in, a.time_out, a.overtime_in, a.overtime_out,
st.shift_start, st.shift_end
FROM attendance a
JOIN employee_info ei ON a.employee_id = ei.employee_id
JOIN departments d ON ei.department_id = d.department_id
LEFT JOIN employee_shifts es ON a.employee_id = es.employee_id
LEFT JOIN shift_types st ON es.shift_type_id = st.shift_type_id
WHERE DATE(a.attendance_date) = CURDATE()";

// Append conditions based on selected filters
if ($selected_department != 'all-departments') {
    $sql .= " AND ei.department_id = " . intval($selected_department);
}
if ($selected_shift != 'all-shifts') {
    $sql .= " AND es.shift_type_id = " . intval($selected_shift);
}

// Execute the query to fetch attendance records
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Calculate hours dynamically based on time_in/time_out
        if (!is_null($row['time_in']) && is_null($row['time_out'])) {
            $shift_start = new DateTime($row['shift_start']);
            $shift_end = new DateTime($row['shift_end']);
            $total_shift_hours = $shift_start->diff($shift_end)->h + ($shift_start->diff($shift_end)->i / 60);
            $row['worked_hours'] = number_format($total_shift_hours / 2, 2);
            $row['early_arrival_hours'] = '0.00';
            $row['late_departure_hours'] = '0.00';
            $row['total_overtime_hours'] = '0.00';
        } elseif (!is_null($row['time_out'])) {
            $time_in = new DateTime($row['time_in']);
            $time_out = new DateTime($row['time_out']);
            $shift_start = new DateTime($row['shift_start']);
            $shift_end = new DateTime($row['shift_end']);

            // Calculate worked hours
            $worked_hours = max($shift_start, $time_in)->diff(min($shift_end, $time_out))->h +
                            max($shift_start, $time_in)->diff(min($shift_end, $time_out))->i / 60;

            // Early arrival and late departure
            $early_arrival_hours = ($time_in < $shift_start) ? $shift_start->diff($time_in)->h + $shift_start->diff($time_in)->i / 60 : 0;
            $late_departure_hours = ($time_out > $shift_end) ? $time_out->diff($shift_end)->h + $time_out->diff($shift_end)->i / 60 : 0;

            $row['worked_hours'] = number_format($worked_hours, 2);
            $row['early_arrival_hours'] = number_format($early_arrival_hours, 2);
            $row['late_departure_hours'] = number_format($late_departure_hours, 2);
            $row['total_overtime_hours'] = number_format($early_arrival_hours + $late_departure_hours, 2);
        } else {
            $row['worked_hours'] = '0.00';
            $row['early_arrival_hours'] = '0.00';
            $row['late_departure_hours'] = '0.00';
            $row['total_overtime_hours'] = '0.00';
        }

        $attendance_records[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Timesheet</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/timesheet_report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body class="light-mode">
<div class="top-nav">
    <ul>
        <a href="../maindashboard.php">
            <h1 class="logopos">
                
                Paradise <br> Hotel
            </h1>
        </a>
        <li class="top">
            <a class="top1" href="">
                <i class="fas fa-home"></i> <!-- Icon for Home -->
                Home
            </a>
            <div class="top1dropdown">
                <div class="dropdown-column">
                    <h3>Payroll</h3> <!-- Icon for Payroll -->
                    <a href="time-and-attendance-home.php">
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
            <a class="top1" href="time-and-attendance-home.php">
                <i class="fas fa-chart-line"></i> <!-- Icon for Dashboard -->
                Dashboard
            </a>          
        </li>
        <li class="top">
            <a class="top1" href="">
                <i class="fas fa-tasks"></i> <!-- Icon for Manage -->
                Manage
            </a>
            <div class="top1dropdown">
                <div class="dropdown-column">
                    <h3><b>Attendance Tracking</b></h3> <!-- Icon for Attendance Tracking -->
                    <a href="clocking-system.php">
                        <i class="fas fa-clock"></i> Clocking System <!-- Icon for Clocking System -->
                    </a>
                    <a href="timesheet.php">
                        <i class="fas fa-calendar-alt"></i> Daily Record <!-- Icon for Daily Record -->
                    </a>
                    <a href="attendance-summary.php">
                        <i class="fas fa-list"></i> Attendance Summary <!-- Icon for Attendance Summary -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Leave Management</b></h3> <!-- Icon for Leave Management -->
                    <a href="leavemanagement.php">
                        <i class="fas fa-envelope-open-text"></i> Leave Requests <!-- Icon for Leave Requests -->
                    </a>
                    <a href="leave-record.php">
                        <i class="fas fa-file-alt"></i> Employee Leave Records <!-- Icon for Leave Records -->
                    </a>
                    <a href="leave-type-list.php">
                        <i class="fas fa-list-alt"></i> List of Leave Types <!-- Icon for Leave Types -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Shift Management</b></h3> <!-- Icon for Shift Management -->
                    <a href="manage-shift.php">
                        <i class="fas fa-calendar"></i> Manage Shift <!-- Icon for Manage Shift -->
                    </a>
                    <a href="shift-types.php">
                        <i class="fas fa-layer-group"></i> Shift Types <!-- Icon for Shift Types -->
                    </a>
                </div>
                <div class="dropdown-column">
                    <h3><b>Compliance & Labor Law Adherence</b></h3> <!-- Icon for Compliance -->
                    <a href="../admin/compliance/violations.php">
                        <i class="fas fa-exclamation-triangle"></i> Violations <!-- Icon for Violations -->
                    </a>
                    <a href="../admin/compliance/compliance-report.php">
                        <i class="fas fa-file-contract"></i> Compliance Report <!-- Icon for Compliance Report -->
                    </a>
                    <a href="../admin/compliance/labor-policies.php">
                        <i class="fas fa-book"></i> Labor Policies <!-- Icon for Labor Policies -->
                    </a>
                    <a href="../admin/compliance/adherence-monitoring.php">
                        <i class="fas fa-eye"></i> Adherence Monitoring <!-- Icon for Monitoring -->
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
    <button type="button" id="darkModeToggle" class="dark-mode-toggle" aria-label="Toggle Dark Mode">
        <i class="fas fa-moon"></i> <!-- Icon for dark mode toggle -->
    </button>

    <!-- USER -->
    <div class="admin-section">
        <div class="admin-name">
            <i class="fas fa-user"></i> User - <?php echo htmlspecialchars($username); ?>
            <div class="admin-dropdown-content">
                <a href="../manage_account.php">Manage Account</a>
            </div>
        </div>
    </div>
    <button type="button" class="logout" id="logout-button" style="margin-right: 10px;">
        <i class="fas fa-sign-out-alt"></i> <!-- Icon for logout -->
    </button>
</div>
<!-- END OF TOP NAV BAR -->
<div id="date-info" style="margin-top: 10px;"></div> <!-- Placeholder for date and day -->

<div class="container-daily-attendance">
    <h2>Employee Timesheet</h2>

    <label for="department">Select Department:</label>
    <select name="department" id="department" onchange="updateAttendance()">
        <option value="all-departments">All Departments</option>
        <?php foreach ($departments as $department): ?>
            <option value="<?php echo $department['department_id']; ?>" <?php echo $selected_department == $department['department_id'] ? 'selected' : ''; ?>>
                <?php echo $department['department_name']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="shift">Select Shift:</label>
    <select name="shift" id="shift" onchange="updateAttendance()">
        <option value="all-shifts">All Shifts</option>
        <?php foreach ($shift_types as $shift): ?>
            <option value="<?php echo $shift['shift_type_id']; ?>" <?php echo $selected_shift == $shift['shift_type_id'] ? 'selected' : ''; ?>>
                <?php echo $shift['shift_name']; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<hr>
<h3>Today's Attendance</h3>

<div class="attendance-table">
    <?php if (!empty($attendance_records)): ?>
        <table>
            <tr>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Date</th>
                <th>Shift Start</th> <!-- New column -->
                <th>Shift End</th>   <!-- New column -->
                <th>Time In</th>
                <th>Time Out</th>
                <th>Overtime In</th> <!-- New column -->
                <th>Overtime Out</th> <!-- New column -->
                <th>Status</th>
            </tr>
            <?php foreach ($attendance_records as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['employee_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['department_name']); ?></td>
                    <td><?php echo date("F j, Y", strtotime($record['attendance_date'])); ?></td>
                    <td>
                        <?php echo !empty($record['shift_start']) ? date("g:i A", strtotime($record['shift_start'])) : 'N/A'; ?>
                    </td> <!-- Display shift start -->
                    <td>
                        <?php echo !empty($record['shift_end']) ? date("g:i A", strtotime($record['shift_end'])) : 'N/A'; ?>
                    </td>   <!-- Display shift end -->
                    <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                    <td><?php echo htmlspecialchars($record['time_out']); ?></td>
                    <td><?php echo htmlspecialchars($record['overtime_in']); ?></td>
                    <td><?php echo htmlspecialchars($record['overtime_out']); ?></td>
                    <td><?php echo htmlspecialchars($record['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No attendance records found for today.</p>
    <?php endif; ?>
</div>


<script>
function updateAttendance() {
    const department = document.getElementById('department').value;
    const shift = document.getElementById('shift').value;

    // Reload the page with updated filters
    window.location.href = `?department=${department}&shift=${shift}`;
}
</script>
<script src="../js/time-ph.js"></script>
<script src="../js/sign_out.js"></script>
<script src="../js/no-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>

</body>
</html>
