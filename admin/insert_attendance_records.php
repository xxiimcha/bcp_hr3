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
$selected_department = '';
$selected_shift = '';
$selected_from_date = '';
$selected_to_date = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_department = $_POST['department'];
    $selected_shift = $_POST['shift'];
    $selected_from_date = $_POST['from_date'] ?? '';
    $selected_to_date = $_POST['to_date'] ?? '';

    // Build SQL query
    $sql = "SELECT a.attendance_id, a.employee_id, ei.employee_name, d.department_name, ei.position, 
            a.attendance_date, a.status, a.time_in, a.time_out, a.overtime_in, a.overtime_out,
            st.shift_start, st.shift_end
            FROM attendance a
            JOIN employee_info ei ON a.employee_id = ei.employee_id
            JOIN departments d ON ei.department_id = d.department_id
            LEFT JOIN employee_shifts es ON a.employee_id = es.employee_id
            LEFT JOIN shift_types st ON es.shift_type_id = st.shift_type_id";

    $sql .= " WHERE 1=1";

    // Append conditions based on selected filters
    if ($selected_department != 'all-departments') {
        $sql .= " AND ei.department_id = " . intval($selected_department);
    }
    if ($selected_shift != 'all-shifts') {
        $sql .= " AND es.shift_type_id = " . intval($selected_shift);
    }
    if (!empty($selected_from_date) && !empty($selected_to_date)) {
        $sql .= " AND a.attendance_date BETWEEN '$selected_from_date' AND '$selected_to_date'";
    }

    // Execute the query to fetch attendance records
    $result = $conn->query($sql);
    // Loop through the results and set attendance information
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!is_null($row['time_in']) && is_null($row['time_out'])) {
                $shift_start = new DateTime($row['shift_start']);
                $shift_end = new DateTime($row['shift_end']);
                $total_shift_hours = $shift_start->diff($shift_end)->h + ($shift_start->diff($shift_end)->i / 60);
                $worked_hours = $total_shift_hours / 2;
                $row['worked_hours'] = number_format($worked_hours, 2);
                $row['early_arrival_hours'] = '0.00';
                $row['late_departure_hours'] = '0.00';
                $row['total_overtime_hours'] = '0.00';
            } elseif (!is_null($row['time_out'])) {
                $time_in = new DateTime($row['time_in']);
                $time_out = new DateTime($row['time_out']);
                $shift_start = new DateTime($row['shift_start']);
                $shift_end = new DateTime($row['shift_end']);

                // Calculate hours worked only during the shift
                $shift_work_start = max($time_in, $shift_start);
                $shift_work_end = min($time_out, $shift_end);
                $worked_hours = $shift_work_start->diff($shift_work_end)->h + ($shift_work_start->diff($shift_work_end)->i / 60);

                // Calculate early arrival and late departure hours separately
                $early_arrival_hours = ($time_in < $shift_start) ? $shift_start->diff($time_in)->h + ($shift_start->diff($time_in)->i / 60) : 0;
                $late_departure_hours = ($time_out > $shift_end) ? $time_out->diff($shift_end)->h + ($time_out->diff($shift_end)->i / 60) : 0;

                $total_overtime_hours = $early_arrival_hours + $late_departure_hours;

                $row['worked_hours'] = number_format($worked_hours, 2);
                $row['early_arrival_hours'] = number_format($early_arrival_hours, 2);
                $row['late_departure_hours'] = number_format($late_departure_hours, 2);
                $row['total_overtime_hours'] = number_format($total_overtime_hours, 2);
            } else {
                $row['worked_hours'] = '0.00';
                $row['early_arrival_hours'] = '0.00';
                $row['late_departure_hours'] = '0.00';
                $row['total_overtime_hours'] = '0.00';
            }

            $attendance_records[] = $row;
        }
    }

    // Insert attendance records into attendance_records table
    $insert_sql = "
        INSERT INTO attendance_records (
            employee_id, 
            attendance_date, 
            time_in, 
            time_out, 
            overtime_in, 
            overtime_out, 
            worked_hours, 
            early_arrival_hours, 
            late_departure_hours, 
            total_overtime_hours, 
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param('issssssssss', $employee_id, $attendance_date, $time_in, $time_out, $overtime_in, $overtime_out, $worked_hours, $early_arrival_hours, $late_departure_hours, $total_overtime_hours, $status);

    foreach ($attendance_records as $record) {
        $employee_id = $record['employee_id'];
        $attendance_date = $record['attendance_date'];
        $time_in = $record['time_in'];
        $time_out = $record['time_out'];
        $overtime_in = $record['overtime_in'];
        $overtime_out = $record['overtime_out'];
        $worked_hours = $record['worked_hours'];
        $early_arrival_hours = $record['early_arrival_hours'];
        $late_departure_hours = $record['late_departure_hours'];
        $total_overtime_hours = $record['total_overtime_hours'];
        $status = $record['status'];

        $stmt->execute();
    }

    $stmt->close();

    // Group attendance records by date
    $attendance_by_date = [];
    foreach ($attendance_records as $record) {
        $attendance_date = $record['attendance_date'];
        if (!isset($attendance_by_date[$attendance_date])) {
            $attendance_by_date[$attendance_date] = [];
        }
        $attendance_by_date[$attendance_date][] = $record;
    }

    // Insert "Absent" attendance for employees with shifts but no attendance for today
    $today_date = date('Y-m-d');
    $absent_employees_sql = "
        SELECT ei.employee_id, ei.employee_name, es.shift_type_id, st.shift_start, st.shift_end
        FROM employee_shifts es
        JOIN employee_info ei ON es.employee_id = ei.employee_id
        LEFT JOIN attendance a ON ei.employee_id = a.employee_id AND a.attendance_date = '$today_date'
        LEFT JOIN shift_types st ON es.shift_type_id = st.shift_type_id
        WHERE a.attendance_id IS NULL
        AND (ei.employee_id NOT IN (
            SELECT employee_id FROM attendance WHERE attendance_date = '$today_date' AND status = 'Leave'
        ))
    ";

    $absent_employees_result = $conn->query($absent_employees_sql);

    if ($absent_employees_result && $absent_employees_result->num_rows > 0) {
        while ($absent_employee = $absent_employees_result->fetch_assoc()) {
            $employee_id = $absent_employee['employee_id'];
            $insert_attendance_sql = "
                INSERT INTO attendance (employee_id, attendance_date, status)
                VALUES ('$employee_id', '$today_date', 'Absent')
            ";
            $conn->query($insert_attendance_sql);
        }
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
<body class="light-mode"> <!-- Initially setting light mode -->

<div id="date-info" style="margin-top: 10px;"></div> <!-- Placeholder for date and day -->

<div class="container-daily-attendance">

    <h2>Employee Timesheet</h2>

    <form method="POST">
    <label for="department">Select Department:</label>
    <select name="department" id="department">
        <option value="all-departments">All Departments</option>
        <?php foreach ($departments as $department): ?>
            <option value="<?php echo $department['department_id']; ?>" <?php echo $selected_department == $department['department_id'] ? 'selected' : ''; ?>>
                <?php echo $department['department_name']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="shift">Select Shift:</label>
    <select name="shift" id="shift">
        <option value="all-shifts">All Shifts</option>
        <?php foreach ($shift_types as $shift): ?>
            <option value="<?php echo $shift['shift_type_id']; ?>" <?php echo $selected_shift == $shift['shift_type_id'] ? 'selected' : ''; ?>>
                <?php echo $shift['shift_name']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="from_date">From:</label>
    <input type="date" name="from_date" id="from_date" value="<?php echo htmlspecialchars($selected_from_date ?? ''); ?>">

    <label for="to_date">To:</label>
    <input type="date" name="to_date" id="to_date" value="<?php echo htmlspecialchars($selected_to_date ?? ''); ?>">

    <button type="submit" class="get-employee-btn">Generate</button>
</form>

    </div>

    <h3><?php echo isset($week_display) ? htmlspecialchars($week_display) : 'All Attendance Records'; ?></h3>

    <?php if (!empty($attendance_by_date)): ?>
        <table>
        <tr>
            <th>Employee Name</th>
            <th>Department</th>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Worked Hours</th>
            <th>Overtime In</th> <!-- New column -->
            <th>Overtime Out</th> <!-- New column -->
            <th>Early Arrival Hours</th>
            <th>Late Departure Hours</th>
            <th>Total Overtime Hours</th>
            <th>Status</th>
        </tr>

        <?php foreach ($attendance_by_date as $date => $records): ?>
            <tr>
        <td colspan="12" class="table-divider"><?php echo date("F j, Y", strtotime($date)); ?></td>
    </tr>
    <?php foreach ($records as $record): ?>
        <tr>
            <td><?php echo htmlspecialchars($record['employee_name']); ?></td>
            <td><?php echo htmlspecialchars($record['department_name']); ?></td>
            <td><?php echo date("F j, Y", strtotime($record['attendance_date'])); ?></td>

            <?php
                // Check if the status is 'Leave'
                $is_leave = ($record['status'] == 'Leave');
            ?>

            <td>
                <input type="time" class="editable" data-id="<?php echo $record['attendance_id']; ?>" data-field="time_in" value="<?php echo htmlspecialchars($record['time_in']); ?>" <?php echo $is_leave ? 'disabled' : ''; ?>>
            </td>

            <td>
                <input type="time" class="editable" data-id="<?php echo $record['attendance_id']; ?>" data-field="time_out" value="<?php echo htmlspecialchars($record['time_out']); ?>" <?php echo $is_leave ? 'disabled' : ''; ?>>
            </td>

            <td><?php echo $record['worked_hours']; ?> hrs</td>
            <td><?php echo htmlspecialchars($record['overtime_in']); ?></td>
            <td><?php echo htmlspecialchars($record['overtime_out']); ?></td>
            <td><?php echo htmlspecialchars($record['early_arrival_hours']); ?> hrs</td>
            <td><?php echo htmlspecialchars($record['late_departure_hours']); ?> hrs</td>
            <td><?php echo htmlspecialchars($record['total_overtime_hours']); ?> hrs</td>
            <td><?php echo htmlspecialchars($record['status']); ?></td>
        </tr>

            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>

        <div style="margin-top: 5px; text-align: right; margin-right: 5px;">
           
        </div>

    <?php else: ?>
        <p>No attendance records found for the selected criteria.</p>
    <?php endif; ?>

<style>

</style>

    <script>
        document.querySelectorAll('.editable').forEach(input => {
    input.addEventListener('change', function() {
        const attendanceId = this.getAttribute('data-id');
        const field = this.getAttribute('data-field');
        const value = this.value;

        // Send AJAX request to update the attendance
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_attendance.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                // Update the hours dynamically on the table
                document.querySelector(`input[data-id="${attendanceId}"][data-field="time_in"]`).value = data.time_in;
                document.querySelector(`input[data-id="${attendanceId}"][data-field="time_out"]`).value = data.time_out;
                // Update worked hours and overtime
                const row = input.closest('tr');
                row.querySelector('td:nth-child(6)').innerText = data.worked_hours + ' hrs';
                row.querySelector('td:nth-child(11)').innerText = data.total_overtime_hours + ' hrs';
            }
        };
        xhr.send(`attendance_id=${attendanceId}&field=${field}&value=${value}`);
    });
});

    </script>
<script>
document.querySelector('.get-employee-btn').addEventListener('click', function() {
    // Get the timesheet container
    var timesheetContainer = document.querySelector('.timesheet-container');
    
    // Toggle the display property between 'none' and 'block'
    if (timesheetContainer.style.display === 'none' || timesheetContainer.style.display === '') {
        timesheetContainer.style.display = 'block';
    } else {
        timesheetContainer.style.display = 'none';
    }
});




</script>


      <style>
        .table-divider {
      text-align:center; background-color:#8eb69b;}
      body.dark-mode .table-divider {
        text-align:center; background-color:#0b2b26;}
      </style>
<footer>
    <p>2024 Timesheet Tracker</p>
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

<script src="../js/time-ph.js"></script>
<script src="../js/sign_out.js"></script>
<script src="../js/no-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>

</body>

</html>






after i click the generate button it will generate a table right? now, create a function that will insert the data on a table to a new table named attendance records 