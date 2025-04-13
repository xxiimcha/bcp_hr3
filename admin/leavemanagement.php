<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch employee data from the external API
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$employee_data = [];
$leaveTypesResult = $conn->query("SELECT leave_id, leave_type FROM leave_types");

// Fetch API data
$response = file_get_contents($api_url);
if ($response !== false) {
    $decoded = json_decode($response, true);
    if (isset($decoded['data'])) {
        $employee_data = array_filter($decoded['data'], function ($emp) {
            return isset($emp['status']) && $emp['status'] === 'DoneTraining';
        });
    }
}

// Fetch leave requests (still from local database)
$leaveRequestsResult = $conn->query("SELECT lr.employee_id, lr.leave_id, lt.leave_type, lr.start_date, lr.end_date, 
                                        lr.total_days, lr.remarks, lr.status, lr.date_submitted  
                                     FROM employee_leave_requests lr
                                     JOIN leave_types lt ON lr.leave_id = lt.leave_id");

$conn->close();

// Map API employee info by employee_no for quick lookup
$employee_map = [];
foreach ($employee_data as $emp) {
    $employee_map[$emp['employee_no']] = $emp;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/leave_requestss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode">
<?php include '../partials/nav.php'; ?>
<!-- END OF TOP NAV BAR -->

    <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>


    <!-- Hidden Leave Request Form (Modal Overlay) -->
    <div class="overlay" id="overlay" style="display:none;">
        <div class="leave-request-form">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Leave Request Form</h3>
                <button class="close-btn" onclick="closeForm()">X</button>
            </div>

            

            <form method="POST" action="submit-leave-request.php" id="leaveForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="employee_id">Select Employee:</label>
                        <select name="employee_id" id="employee_id" onchange="fetchEmployeeDetails()" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employee_data as $emp): ?>
                                <option value="<?= htmlspecialchars($emp['employee_no']) ?>">
                                    <?= htmlspecialchars($emp['employee_no'] . ' - ' . $emp['firstname'] . ' ' . $emp['lastname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="leave_type">Leave Type:</label>
                        <select id="leave_type" name="leave_type" required>
                            <option value="">Select Leave Type</option>
                            <?php
                            if ($leaveTypesResult->num_rows > 0) {
                                while ($row = $leaveTypesResult->fetch_assoc()) {
                                    echo '<option value="' . $row['leave_id'] . '">' . htmlspecialchars($row['leave_type']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="employee_name">Employee Name:</label>
                        <input type="text" id="employee_name" name="employee_name" readonly>
                    </div>

                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" name="department" readonly>
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="position">Position:</label>
                        <input type="text" id="position" name="position" readonly>
                    </div>

                    <div class="form-group">
                        <label for="total_days">Total Day/s:</label>
                        <input type="number" id="total_days" name="total_days" readonly>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="remarks">Remarks:</label>
                    <textarea id="remarks" name="remarks" rows="4" placeholder="Comment" required></textarea>
                </div>

                <div class="form-row" style="display: none;">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Reject</option>
                        </select>
                    </div>
                </div>

                <center><button type="submit">Submit Leave Request</button></center>
            </form>
        </div>
    </div>
</div>



<div class="panel">

<div class="flex-container">
    <h3>Pending Request</h3>
    <button type="button" class="view" id="viewListButton">
    <i class="fas fa-plus"></i>
</button>
        </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Date Submitted</th>
                <th>Remarks</th>
                <th>Status</th>
                <th>Change Status</th>

            </tr>
        </thead>

        <!-- inside <tbody> of HTML -->
        <tbody>
        <?php
        if ($leaveRequestsResult->num_rows > 0) {
            while ($row = $leaveRequestsResult->fetch_assoc()) {
                $emp_id = $row['employee_id'];
                $employee = isset($employee_map[$emp_id]) ? $employee_map[$emp_id] : null;

                echo '<tr>';
                echo '<td>' . htmlspecialchars($emp_id) . '</td>';
                echo '<td>' . htmlspecialchars($employee ? $employee['firstname'] . ' ' . $employee['lastname'] : 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($employee['department'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($employee['position'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['leave_type']) . '</td>';
                echo '<td>' . htmlspecialchars($row['start_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['end_date']) . '</td>';
                echo '<td>' . htmlspecialchars($row['total_days']) . '</td>';
                echo '<td>' . htmlspecialchars($row['date_submitted']) . '</td>';
                echo '<td>' . htmlspecialchars($row['remarks']) . '</td>';
                echo '<td>' . htmlspecialchars($row['status']) . '</td>';

                echo '<td><form method="POST" action="update-leave-status.php" class="form-update">
                            <input type="hidden" name="employee_id" value="' . htmlspecialchars($emp_id) . '">
                            <input type="hidden" name="leave_id" value="' . htmlspecialchars($row['leave_id']) . '">
                            <input type="hidden" name="start_date" value="' . htmlspecialchars($row['start_date']) .'">
                            <input type="hidden" name="end_date" value="' . htmlspecialchars($row['end_date']) . '">
                            <input type="hidden" name="total_days" value="' . htmlspecialchars($row['total_days']) . '">
                            <select name="status" required>
                                <option value="" disabled selected>Select Status</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                            <button type="submit" class="update" >Update</button>
                        </form></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="12">No leave requests found.</td></tr>';
        }
        ?>
        </tbody>

    </table>
</div>

<script>
    const viewListButton = document.getElementById('viewListButton');
    const overlay = document.getElementById('overlay');

    viewListButton.addEventListener('click', () => {
        overlay.style.display = 'flex';
    });

    function closeForm() {
        overlay.style.display = 'none';
    }
</script>

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


    <script src="../js/fetch&calculate_leave.js"></script>
    <script src="../js/no-previousbutton.js"></script>
    <script src="../js/sign_out.js"></script>
<script src="../js/toggle-darkmode.js"></script>


<script>
    async function fetchEmployeeDetails() {
        const selectedId = document.getElementById('employee_id').value;

        if (!selectedId) {
            clearEmployeeDetails();
            return;
        }

        try {
            const res = await fetch('https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs');
            const data = await res.json();

            if (data && Array.isArray(data.data)) {
                const employee = data.data.find(emp => emp.employee_no === selectedId);

                if (employee) {
                    document.getElementById('employee_name').value = `${employee.firstname} ${employee.lastname}`;
                    document.getElementById('department').value = employee.department || '';
                    document.getElementById('position').value = employee.position || '';
                } else {
                    clearEmployeeDetails();
                    alert("Employee not found.");
                }
            } else {
                alert("Invalid API response.");
            }
        } catch (error) {
            console.error("API fetch error:", error);
            clearEmployeeDetails();
            alert("Failed to fetch employee details.");
        }
    }

    function clearEmployeeDetails() {
        document.getElementById('employee_name').value = '';
        document.getElementById('department').value = '';
        document.getElementById('position').value = '';
    }
</script>

</body>
<style>/* Style for the Fetch button */
#fetchButton {
    display: inline-block;
    margin-top: 10px;
    padding: 7px 15px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    background-color: #007bff; /* Primary blue */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

/* Hover effect for the button */
#fetchButton:hover {
    background-color: #0056b3; /* Darker blue */
    transform: scale(1.05); /* Slightly enlarge the button */
}

/* Active effect for the button */
#fetchButton:active {
    background-color: #003f7f; /* Even darker blue */
    transform: scale(0.95); /* Slightly shrink the button */
}


</style>

</html>