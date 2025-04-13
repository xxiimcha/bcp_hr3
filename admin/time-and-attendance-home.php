<?php

include '../config.php';

$query = "SELECT start_date, end_date FROM employee_leave_records WHERE status = 'Approved'";
$result = $conn->query($query);

$leave_counts = [];

while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['start_date']);
    $end = new DateTime($row['end_date']);
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($start, $interval, $end->modify('+1 day'));

    foreach ($daterange as $date) {
        $formatted = $date->format('Y-m-d');
        if (!isset($leave_counts[$formatted])) {
            $leave_counts[$formatted] = 1;
        } else {
            $leave_counts[$formatted]++;
        }
    }
}

$formatted_data = [];
foreach ($leave_counts as $date => $count) {
    $formatted_data[] = ['ds' => $date, 'y' => $count];
}

// Call Flask API hosted on Render
$api_url = "https://hr3-ai.onrender.com/predict-leave-trends"; // Replace with your deployed URL
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($formatted_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

$forecast = json_decode($response, true);

// Debug: Check what's actually returned
if (!is_array($forecast)) {
    echo "<script>console.warn('Forecast data is not an array:', " . json_encode($forecast) . ");</script>";
}

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Include the database configuration file

// Fetch counts from the relevant tables
$departmentCount = $conn->query("SELECT COUNT(*) FROM departments")->fetch_row()[0];
$leaveTypeCount = $conn->query("SELECT COUNT(*) FROM leave_types")->fetch_row()[0];
$leaveRequestCount = $conn->query("SELECT COUNT(*) FROM employee_leave_requests")->fetch_row()[0];
$employeeCount = $conn->query("SELECT COUNT(*) FROM employee_info")->fetch_row()[0];
// Fetch the most recent leave requests
$recentLeaveRequests = $conn->query("SELECT * FROM employee_leave_requests ORDER BY date_submitted DESC LIMIT 5");

$todayLeaveRequests = $conn->query("SELECT * FROM employee_leave_requests WHERE DATE(date_submitted) = CURDATE() AND TIME(date_submitted) = CURTIME()");

$employeeShiftsCount = $conn->query("SELECT COUNT(*) FROM emp_shifts")->fetch_row()[0];

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Time and Attendance</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include '../partials/nav.php'; ?>

<main>
    <div class="dashboard-panel">
        <h2 style="text-align: left;">Dashboard</h2>        <hr>
        <br>
        <div class="dashboard-boxes">

            <a href="leave-type-list.php" class="dashboard-box">
                <i class="fas fa-calendar-alt"></i> <!-- Icon for Leave Types -->
                <h3>Leave Types</h3>
                <p><?php echo htmlspecialchars($leaveTypeCount); ?></p>
            </a>
            <a href="leavemanagement.php" class="dashboard-box">
                <i class="fas fa-user-check"></i> <!-- Icon for Leave Requests -->
                <h3>Leave Requests</h3>
                <p><?php echo htmlspecialchars($leaveRequestCount); ?></p>
            </a>
            <a href="../employee-information/employee-list.php" class="dashboard-box">
                <i class="fas fa-users"></i> <!-- Icon for Employees -->
                <h3>Total Employees</h3>
                <p><?php echo htmlspecialchars($employeeCount); ?></p>
            </a>
            <a href="shift-types.php" class="dashboard-box">
        <i class="fas fa-layer-group"></i> <!-- Icon for Shift Types -->
        <h3>Shifts</h3>
        <p><?php echo htmlspecialchars($employeeShiftsCount); ?></p>
    </a>
        </div>


<!-- Display Recent Leave Requests -->
<div class="recent-leave-requests">
    <h3>Recent Leave Requests</h3>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Date Submitted</th> <!-- New Column for Date Submitted -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $recentLeaveRequests->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                    <td><?php 
                        // Format the date_submitted for better readability
                        echo date("F j, Y, g:i a", strtotime($row['date_submitted'])); 
                    ?></td> <!-- Display Date Submitted in Readable Format -->
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


    
</div>

<div class="forecast-panel">
    <h3>Leave Forecast (Next 30 Days)</h3>
    <canvas id="leaveForecastChart" style="max-width: 100%; height: 300px;"></canvas>
</div>

</main>


<style>

</style>



<?php include '../partials/foot.php'; ?>
<script>
const forecastData = <?php echo json_encode($forecast); ?>;
const labels = forecastData.map(item => item.ds);
const data = forecastData.map(item => item.yhat);

console.log("Forecast data received from PHP:", forecastData);

if (Array.isArray(forecastData)) {
    const labels = forecastData.map(item => item.ds);
    const data = forecastData.map(item => item.yhat);

    new Chart(document.getElementById("leaveForecastChart"), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Predicted Leave Count',
                data: data,
                fill: false,
                borderColor: 'blue',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Date' }},
                y: { title: { display: true, text: 'Leave Count' }, beginAtZero: true }
            }
        }
    });
} else {
    document.getElementById("leaveForecastChart").outerHTML = "<p style='color: red;'>Unable to load forecast chart. Invalid data format returned from AI API.</p>";
}
</script>

<footer>
            <p>HRMS3 Dashboard</p>
        </footer>
</body>
</html>
