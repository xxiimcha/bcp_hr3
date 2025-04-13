<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];
$employee_info = null;
$leave_records = [];
$leave_balances = [];
$message = "";

// Get employee_no from URL
if (isset($_GET['employee_id'])) {
    $employee_no = $_GET['employee_id'];

    // Fetch employee data from API
    $api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
    $response = file_get_contents($api_url);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (isset($data['data'])) {
            foreach ($data['data'] as $emp) {
                if ($emp['employee_no'] === $employee_no) {
                    $employee_info = $emp;
                    break;
                }
            }
        }
    }

    // Fetch leave records
    $leave_query = "
        SELECT el.record_id, el.leave_id, lt.leave_type, el.start_date, el.end_date, el.total_days, el.remarks, el.status 
        FROM employee_leave_records el
        JOIN leave_types lt ON el.leave_id = lt.leave_id
        WHERE el.employee_id = ?";
    $leave_stmt = $conn->prepare($leave_query);
    $leave_stmt->bind_param("s", $employee_no);
    $leave_stmt->execute();
    $leave_result = $leave_stmt->get_result();
    $leave_records = $leave_result->fetch_all(MYSQLI_ASSOC);
    $leave_stmt->close();

    // Fetch leave balances
    $balance_query = "
        SELECT elb.leave_id, lt.leave_type, elb.balance 
        FROM employee_leave_balances elb
        JOIN leave_types lt ON elb.leave_id = lt.leave_id
        WHERE elb.employee_id = ?";
    $balance_stmt = $conn->prepare($balance_query);
    $balance_stmt->bind_param("s", $employee_no);
    $balance_stmt->execute();
    $balance_result = $balance_stmt->get_result();
    $leave_balances = $balance_result->fetch_all(MYSQLI_ASSOC);
    $balance_stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave - <?php echo htmlspecialchars($employee_info['employee_name'] ?? 'Employee'); ?></title> 
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/employee-leave-records.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<main>
<?php if (!empty($message)): ?>
    <div class="message-panel">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (isset($employee_info)): ?>
    <div class="employee-info-panel">
        <a href="leave-record.php" class="back-button"><i class="fas fa-arrow-left"></i></a>
        <center><h2>Employee Information</h2><br></center>
        <form class="employee-info-form">
            <div class="form-group">
                <div class="form-column">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" value="<?= htmlspecialchars($employee_info['employee_no']) ?>" readonly>

                    <label for="employee_name">Employee Name</label>
                    <input type="text" id="employee_name" value="<?= htmlspecialchars($employee_info['firstname'] . ' ' . $employee_info['lastname']) ?>" readonly>

                    <label for="date_of_birth">Date of Birth</label>
                    <input type="text" id="date_of_birth" value="<?= htmlspecialchars($employee_info['birthdate']) ?>" readonly>

                    <label for="email_address">Email Address</label>
                    <input type="email" id="email_address" value="<?= htmlspecialchars($employee_info['email']) ?>" readonly>
                </div>

                <div class="form-column">
                    <label for="department">Department</label>
                    <input type="text" id="department" value="<?= htmlspecialchars($employee_info['position']) ?>" readonly>

                    <label for="position">Position</label>
                    <input type="text" id="position" value="<?= htmlspecialchars($employee_info['position']) ?>" readonly>

                    <label for="contact_no">Contact No</label>
                    <input type="text" id="contact_no" value="<?= htmlspecialchars($employee_info['number']) ?>" readonly>

                    <label for="address">Address</label>
                    <textarea id="address" readonly><?= htmlspecialchars($employee_info['address']) ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="date_hired">Date Hired</label>
                <input type="text" id="date_hired" value="<?= htmlspecialchars($employee_info['created_at']) ?>" readonly>

                <label for="status">Status</label>
                <input type="text" id="status" value="<?= htmlspecialchars($employee_info['status']) ?>" readonly>
            </div>
        </form>
    </div>

    <!-- Records Panel -->
    <div class="records-panel">
        <h3>Leave Records</h3>
        <table>
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Days</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($leave_records)): ?>
                    <?php foreach ($leave_records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['leave_type']) ?></td>
                            <td><?= htmlspecialchars($record['start_date']) ?></td>
                            <td><?= htmlspecialchars($record['end_date']) ?></td>
                            <td><?= htmlspecialchars($record['total_days']) ?></td>
                            <td><?= htmlspecialchars($record['remarks']) ?></td>
                            <td><?= htmlspecialchars($record['status']) ?></td>
                            <td>
                                <form action="delete-leave-records.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave record?');">
                                    <input type="hidden" name="record_id" value="<?= $record['record_id'] ?>">
                                    <button type="submit" class="delete-button">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No leave records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <center>
    <!-- Leave Balances Panel -->
    <div class="balances-panel">
        <!-- Add Leave Balance Overlay -->
        <div class="balance-overlay" id="balance-overlay">
            <div id="add-leave-balance-form">
                <h4>Add Leave Balance</h4><hr>
                <form id="add-balance-form">
                    <label for="leave_id">Leave Type</label>
                    <select name="leave_code" id="leave_code" required>
                        <option value="">Select Leave Type</option>
                        <?php
                        $leave_types_query = "SELECT leave_id, leave_type FROM leave_types";
                        $leave_types_result = $conn->query($leave_types_query);
                        if ($leave_types_result->num_rows > 0) {
                            while ($row = $leave_types_result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['leave_id']) . '">' . htmlspecialchars($row['leave_type']) . '</option>';
                            }
                        }
                        ?>
                    </select>

                    <label for="balance">Balance</label>
                    <input type="number" name="balance" id="balance" min="1" required>

                    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_info['employee_no']) ?>">

                    <button type="submit">Add Balance</button>
                    <button type="button" id="cancel-btn">Cancel</button>
                </form>
            </div>
        </div>

        <h3>Leave Balances</h3>
        <?php if (!in_array($employee_info['status'], ['Part-time', 'Contractual'])): ?>
            <button id="add-leave-balance-btn">Add Leave Balance</button>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($leave_balances)): ?>
                    <?php foreach ($leave_balances as $balance): ?>
                        <tr>
                            <td><?= htmlspecialchars($balance['leave_type']) ?></td>
                            <td><?= htmlspecialchars($balance['balance']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No leave balances found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </center>

<?php else: ?>
    <p>Employee information not found.</p>
<?php endif; ?>
</main>

<footer>
    <p>2024 Leave Management</p>
</footer>
<script src="../js/sign_out.js"></script>
<script src="../js/toggle-darkmode.js"></script>
<script>src="../js/no-previousbutton.js"</script>
<script>
document.getElementById("add-leave-balance-btn").addEventListener("click", function() {
    document.getElementById("balance-overlay").style.display = "flex"; // Show overlay
});

document.getElementById("cancel-btn").addEventListener("click", function() {
    document.getElementById("balance-overlay").style.display = "none"; // Hide overlay
});

document.getElementById("add-balance-form").addEventListener("submit", function(e) {
    e.preventDefault(); // prevent page reload

    const form = e.target;
    const formData = new FormData(form);

    // Log each individual field being submitted
    console.log("Submitting the following data:");
    formData.forEach((value, key) => {
        console.log(`${key}: ${value}`);
    });

    fetch('add-leave-balance.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        console.log("Server response:", data);
        alert('Leave balance successfully added or updated.');
        window.location.reload(); // optional: reload to refresh the table
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        alert('An error occurred while submitting the form.');
    });
});

</script>
</body>
</html>
