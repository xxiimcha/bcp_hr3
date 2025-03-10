<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Handle form submission for loading employee details
if (isset($_POST['select_employee'])) {
    $employee_id = $_POST['employee_id'];

    // Fetch employee details based on the selected employee ID
    $sql = "SELECT ei.employee_id, ei.employee_name, d.department_name, ei.position, ei.email_address, el.password
            FROM employee_info ei
            LEFT JOIN employee_logins el ON ei.employee_id = el.employee_id
            JOIN departments d ON ei.department_id = d.department_id
            WHERE ei.employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
}

// SQL query to fetch all employees for the dropdown
$sql_employees = "SELECT employee_id, employee_name FROM employee_info";
$result_employees = $conn->query($sql_employees);

// Handle form submission for adding a new employee account
if (isset($_POST['add_employee'])) {
    $employee_id = $_POST['employee_id'];
    $new_password = $_POST['new_password'];
    $error_message = ""; // Initialize error message

    // Validate password requirements
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*!])[A-Za-z\d@#$%^&*!]{8,}$/', $new_password)) {
        $error_message = "Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be a minimum of 8 characters long.";
    }

    // Insert new employee login if no error
    if (empty($error_message)) {
        $sql_check = "SELECT * FROM employee_logins WHERE employee_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $employee_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows === 0) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Insert new login into employee_logins
            $sql_insert = "INSERT INTO employee_logins (employee_id, password) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("is", $employee_id, $hashed_password);

            if ($stmt_insert->execute()) {
                $message = "New account successfully created for employee ID: " . $employee_id; // Set success message
            } else {
                $message = "Error creating new account: " . $conn->error; // Set error message
            }

            $stmt_insert->close();
        } else {
            $message = "Account already exists for employee ID: " . $employee_id; // Set account exists message
        }

        $stmt_check->close();
    } else {
        $message = $error_message; // Set validation error message
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="../css/add_account.css">
    <style>
        /* Error styling */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        .alert {
    background-color: #e7efec; /* Light red background */
    color: #555;            /* Dark red text */
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
}

    </style>
</head>
<body>

<div class="container">
    <h1>Create Employee Account</h1>

    <!-- Employee Selection Form -->
    <form method="POST">
        <label for="employee_id">Select Employee:</label>
        <select name="employee_id" required>
            <option value="">Select Employee</option>
            <?php
            if ($result_employees->num_rows > 0) {
                while ($row = $result_employees->fetch_assoc()) {
                    echo "<option value='" . $row["employee_id"] . "'>" . $row["employee_name"] . "</option>";
                }
            }
            ?>
        </select>
        <button type="submit" name="select_employee">Load Employee</button>
    </form>

    <?php if (!empty($message)): ?>
            <div class="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

    <!-- Button to trigger overlay form -->
    <?php if (isset($employee)): ?>

        <h2>Employee Details</h2>
        <form method="POST">
            <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
            
            <label for="employee_name">Employee Name:</label>
            <input type="text" name="employee_name" value="<?php echo $employee['employee_name']; ?>" readonly>
            
            <label for="department_name">Department:</label>
            <input type="text" name="department_name" value="<?php echo $employee['department_name']; ?>" readonly>

            <label for="position">Position:</label>
            <input type="text" name="position" value="<?php echo $employee['position']; ?>" readonly>

            <label for="email_address">Email Address:</label>
            <input type="email" name="email_address" value="<?php echo $employee['email_address']; ?>" readonly>


        </form>
        <button id="addEmployeeBtn">Create Password</button>

    <?php endif; ?>

    <a href="employee_accounts.php" class="back-link">Back to Employee Accounts</a>
</div>

<!-- Overlay for Add Employee Account Form -->
<div id="overlay" class="overlay" style="display: none;">
    <div class="overlay-content">
        <form id="addEmployeeForm" method="POST">
            <h2>Add Employee Login</h2>
            <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
            <label for="new_password">New Password for New Account:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            <p style="color: red; font-size: 14px;">* Password must contain:</p>
            <ul>
                <li>At least one uppercase letter</li>
                <li>At least one lowercase letter</li>
                <li>At least one number</li>
                <li>At least one special character (e.g., @, #, $, etc.)</li>
                <li>Minimum 8 characters</li>
            </ul>
            <div id="password_error" class="password-instructions error-message"></div>

            <button type="submit" name="add_employee">Add Employee Account</button>
        </form>
    </div>
</div>

<script>
    // Get elements
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const overlay = document.getElementById('overlay');

    // Function to open the overlay
    addEmployeeBtn.addEventListener('click', function() {
        overlay.style.display = 'flex'; // Show the overlay
    });

    // Function to close the overlay when clicking outside of it
    overlay.addEventListener('click', function(event) {
        if (event.target === overlay) {
            overlay.style.display = 'none'; // Hide the overlay
        }
    });

    // Form validation for new password
    const form = document.getElementById('addEmployeeForm');
    const passwordInput = document.getElementById('new_password');
    const passwordError = document.getElementById('password_error');

    form.addEventListener('submit', function(event) {
        // Clear any previous error message
        passwordError.textContent = '';

        // Validate password
        const password = passwordInput.value;
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*!])[A-Za-z\d@#$%^&*!]{8,}$/;

        if (!passwordRegex.test(password)) {
            event.preventDefault();  // Stop form submission
            passwordError.textContent = "Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be a minimum of 8 characters long.";
        }
    });
</script>
<script src="../js/sign_out.js"></script>
<script src="../jsno-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
</body>
</html>


