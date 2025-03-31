<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Load employee data from API
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$employee_data = [];

$response = file_get_contents($api_url);
if ($response !== false) {
    $decoded = json_decode($response, true);
    if (isset($decoded['data'])) {
        $employee_data = $decoded['data'];
    }
}

$employee = null;
$message = "";

// Handle auto-load of employee based on selection
if (isset($_POST['employee_no'])) {
    $employee_no = $_POST['employee_no'];
    foreach ($employee_data as $emp) {
        if ($emp['employee_no'] === $employee_no) {
            $employee = $emp;
            break;
        }
    }
}

// Handle account creation
if (isset($_POST['add_employee'])) {
    $employee_no = $_POST['employee_no'];
    $new_password = $_POST['new_password'];

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*!])[A-Za-z\d@#$%^&*!]{8,}$/', $new_password)) {
        $message = "Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be a minimum of 8 characters long.";
    } else {
        $check_sql = "SELECT * FROM employee_logins WHERE employee_id = '$employee_no'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) === 0) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO employee_logins (employee_id, password) VALUES ('$employee_no', '$hashed_password')";
            if (mysqli_query($conn, $insert_sql)) {
                $message = "New account successfully created for employee ID: $employee_no";
            } else {
                $message = "Error creating account: " . mysqli_error($conn);
            }
        } else {
            $message = "Account already exists for employee ID: $employee_no";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Employee Account</title>
    <link rel="stylesheet" href="../css/add_account.css">
    <style>
        .alert { background-color: #e7efec; color: #555; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .error-message { color: red; font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Create Employee Account</h1>

    <!-- Employee Selection Form (Auto-submits) -->
    <form method="POST" id="employeeForm">
        <label for="employee_no">Select Employee:</label>
        <select name="employee_no" required>
            <option value="">Select Employee</option>
            <?php
            // Step 1: Get all employee_no values from employee_logins
            $existing_accounts = [];
            $result_existing = mysqli_query($conn, "SELECT employee_id FROM employee_logins");
            while ($row = mysqli_fetch_assoc($result_existing)) {
                $existing_accounts[] = $row['employee_id'];
            }
            ?>

            <?php foreach ($employee_data as $emp): ?>
                <?php if (!in_array($emp['employee_no'], $existing_accounts)): ?>
                    <option value="<?= htmlspecialchars($emp['employee_no']) ?>"
                        <?= (isset($employee) && $employee['employee_no'] === $emp['employee_no']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['firstname'] . ' ' . $emp['lastname']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>

        </select>
    </form>

    <script>
        document.querySelector("select[name='employee_no']").addEventListener("change", function () {
            document.getElementById("employeeForm").submit();
        });
    </script>

    <?php if (!empty($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Show Selected Employee Info -->
    <?php if ($employee): ?>
        <h2>Employee Details</h2>
        <form method="POST">
            <input type="hidden" name="employee_no" value="<?= htmlspecialchars($employee['employee_no']) ?>">

            <label>Employee Name:</label>
            <input type="text" value="<?= htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) ?>" readonly>

            <label>Position:</label>
            <input type="text" value="<?= htmlspecialchars($employee['position']) ?>" readonly>

            <label>Email Address:</label>
            <input type="email" value="<?= htmlspecialchars($employee['email']) ?>" readonly>

            <label>Contact No:</label>
            <input type="text" value="<?= htmlspecialchars($employee['number']) ?>" readonly>

            <label>Status:</label>
            <input type="text" value="<?= htmlspecialchars($employee['status']) ?>" readonly>
        </form>

        <button id="addEmployeeBtn">Create Password</button>
    <?php endif; ?>

    <a href="employee_accounts.php" class="back-link">Back to Employee Accounts</a>
</div>

<!-- Overlay Password Form -->
<div id="overlay" class="overlay" style="display: none;">
    <div class="overlay-content">
        <form id="addEmployeeForm" method="POST">
            <h2>Add Employee Login</h2>
            <input type="hidden" name="employee_no" value="<?= htmlspecialchars($employee['employee_no'] ?? '') ?>">

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <p style="color: red;">* Password must contain:</p>
            <ul>
                <li>Uppercase letter</li>
                <li>Lowercase letter</li>
                <li>Number</li>
                <li>Special character (@, #, $, etc.)</li>
                <li>Minimum 8 characters</li>
            </ul>

            <div id="password_error" class="error-message"></div>
            <button type="submit" name="add_employee">Add Employee Account</button>
        </form>
    </div>
</div>

<script>
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const overlay = document.getElementById('overlay');
    const form = document.getElementById('addEmployeeForm');
    const passwordInput = document.getElementById('new_password');
    const passwordError = document.getElementById('password_error');

    addEmployeeBtn?.addEventListener('click', () => {
        overlay.style.display = 'flex';
    });

    overlay?.addEventListener('click', function (e) {
        if (e.target === overlay) {
            overlay.style.display = 'none';
        }
    });

    form?.addEventListener('submit', function (e) {
        passwordError.textContent = '';
        const password = passwordInput.value;
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*!])[A-Za-z\d@#$%^&*!]{8,}$/;

        if (!regex.test(password)) {
            e.preventDefault();
            passwordError.textContent = "Password must meet the required format.";
        }
    });
</script>

<script src="../js/sign_out.js"></script>
<script src="../jsno-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
</body>
</html>
