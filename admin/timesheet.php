<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch external employee data from API
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$api_response = file_get_contents($api_url);
$api_data = json_decode($api_response, true);

$employees = [];
if (isset($api_data['data'])) {
    foreach ($api_data['data'] as $emp) {
        $employees[$emp['employee_no']] = $emp;
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

// Filters
$attendance_records = [];
$selected_shift = $_GET['shift'] ?? 'all-shifts';

// SQL Query to get today's timesheet records
$sql = "SELECT et.*, st.shift_start, st.shift_end
        FROM employee_timesheet et
        LEFT JOIN emp_shifts es ON et.employee_id = es.employee_id
        LEFT JOIN shift_types st ON es.shift_type_id = st.shift_type_id
        WHERE DATE(et.time_in) = CURDATE()";

if ($selected_shift != 'all-shifts') {
    $sql .= " AND es.shift_type_id = " . intval($selected_shift);
}

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
<?php include '../partials/nav.php'; ?>

<div id="date-info" style="margin-top: 10px;"></div>

<div class="container-daily-attendance">
    <h2>Employee Timesheet</h2>

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
                <th>Position</th>
                <th>Date</th>
                <th>Shift Start</th>
                <th>Shift End</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours Worked</th>
                <th>Overtime (Hrs)</th>
                <th>Status</th>
            </tr>
            <?php foreach ($attendance_records as $record): ?>
                <?php
                    $emp = $employees[$record['employee_id']] ?? null;
                    $name = $emp ? $emp['firstname'] . ' ' . $emp['lastname'] : 'Unknown';
                    $position = $emp['position'] ?? 'N/A';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($name); ?></td>
                    <td><?php echo htmlspecialchars($position); ?></td>
                    <td><?php echo date("F j, Y", strtotime($record['time_in'])); ?></td>
                    <td><?php echo !empty($record['shift_start']) ? date("g:i A", strtotime($record['shift_start'])) : 'N/A'; ?></td>
                    <td><?php echo !empty($record['shift_end']) ? date("g:i A", strtotime($record['shift_end'])) : 'N/A'; ?></td>
                    <td><?php echo date("g:i A", strtotime($record['time_in'])); ?></td>
                    <td><?php echo date("g:i A", strtotime($record['time_out'])); ?></td>
                    <td><?php echo htmlspecialchars($record['hours_worked']); ?></td>
                    <td><?php echo htmlspecialchars($record['overtime_hours']); ?></td>
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
    const shift = document.getElementById('shift').value;
    window.location.href = `?shift=${shift}`;
}
</script>

<script src="../js/time-ph.js"></script>
<script src="../js/sign_out.js"></script>
<script src="../js/no-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>

</body>
</html>
