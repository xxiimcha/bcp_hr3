<?php
require 'config.php';
session_start();

$error = '';
$success_message = '';

// Handle admin login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $admin_username = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    $query = "SELECT * FROM admin_users WHERE admin_username = '$admin_username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $admin_username;
            $_SESSION['user_id'] = $row['id'];

            header("Location: " . ($admin_username === 'QRscanner'
                ? "admin/employee-clocking.php"
                : "admin/time-and-attendance-home.php"));
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'No username or password found.';
    }
}

// Handle employee login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['employee_login'])) {
    $employee_id = mysqli_real_escape_string($conn, trim($_POST['employee_id'] ?? ''));
    $password = $_POST['password'] ?? '';

    $query = "SELECT * FROM employee_logins WHERE employee_id = '$employee_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password']) && $row['is_active']) {
            $api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
            $api_response = file_get_contents($api_url);
            $employeesData = json_decode($api_response, true);

            $employee_found = false;
            foreach ($employeesData['data'] as $emp) {
                if ($emp['employee_no'] === $employee_id) {
                    $_SESSION['employee_name'] = $emp['firstname'] . ' ' . $emp['lastname'];
                    $_SESSION['position'] = $emp['position'];
                    $_SESSION['employee_id'] = $emp['employee_no'];
                    $_SESSION['logged_in'] = true;
                    $employee_found = true;
                    break;
                }
            }

            if ($employee_found) {
                session_regenerate_id(true);
                header("Location: employee-portal/portal.php");
                exit();
            } else {
                $error = "Employee record not found in HR API.";
            }
        } else {
            $error = "Invalid password or account is inactive.";
        }
    } else {
        $error = "Employee ID not found.";
    }
}

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $username_or_employee_id = mysqli_real_escape_string($conn, $_POST['username_or_employee_id']);

    function isStrongPassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/', $password);
    }

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!isStrongPassword($new_password)) {
        $error = "Password must be strong (upper/lower/number/special).";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        if ($user_type === 'admin') {
            $check = "SELECT id FROM admin_users WHERE admin_username = '$username_or_employee_id' AND email = '$email'";
            $result = mysqli_query($conn, $check);

            if ($result && mysqli_num_rows($result) > 0) {
                $update = "UPDATE admin_users SET password = '$hashed_password' WHERE admin_username = '$username_or_employee_id' AND email = '$email'";
                if (mysqli_query($conn, $update)) {
                    $success_message = "Admin password successfully reset.";
                } else {
                    $error = "Error updating admin password.";
                }
            } else {
                $error = "Admin username or email not found.";
            }

        } elseif ($user_type === 'employee') {
            $check = "SELECT ei.employee_id 
                      FROM employee_info ei 
                      JOIN employee_logins el ON ei.employee_id = el.employee_id 
                      WHERE ei.employee_id = '$username_or_employee_id' AND ei.email_address = '$email'";
            $result = mysqli_query($conn, $check);

            if ($result && mysqli_num_rows($result) > 0) {
                $update = "UPDATE employee_logins SET password = '$hashed_password' WHERE employee_id = '$username_or_employee_id'";
                if (mysqli_query($conn, $update)) {
                    $success_message = "Employee password successfully reset.";
                } else {
                    $error = "Error updating employee password.";
                }
            } else {
                $error = "Employee ID or email not found.";
            }
        } else {
            $error = "Invalid user type selected.";
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
    <link rel="icon" type="image/webp" href="img/logo.webp">
    <link rel="stylesheet" href="css/index1.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Welcome - Paradise Hotel</title>

</head>
<body class="light-mode">





<style>
    .message {
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    text-align: center;
}

.error {
    background-color: #ff4d4d;
    color: white;
}

.success {
    background-color: #4CAF50;
    color: white;
}

#successMessage, #errorMessage {
    display: none; /* Initially hide the messages */
}

</style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    // Show success or error message if they exist
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');

    // Display success message if exists and hide after 3 seconds
    if (successMessage) {
        successMessage.style.display = 'block'; // Show the message
        setTimeout(function() {
            successMessage.style.display = 'none'; // Hide the message after 3 seconds
        }, 3000); // 3000 milliseconds = 3 seconds
    }

    // Display error message if exists and hide after 3 seconds
    if (errorMessage) {
        errorMessage.style.display = 'block'; // Show the message
        setTimeout(function() {
            errorMessage.style.display = 'none'; // Hide the message after 3 seconds
        }, 4000); // 3000 milliseconds = 3 seconds
    }
});

    </script>


    <!-- Login Form Section -->
    <div class="container" id="loginSection">
    <?php if (!empty($success_message)): ?>
    <div id="successMessage" class="message success"><?= $success_message ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div id="errorMessage" class="message error"><?= $error ?></div>
<?php endif; ?>
        <div class="logo-container">
            <img src="img/logo.webp" class="plogo" alt="Paradise Hotel Logo">
        </div>



        <h1>Welcome Admin<h1>

        <h4 id="loginHeader"></h4>

        <!-- Admin Login Form -->
        <div id="adminLoginForm">
            <form method="POST">
                <p style="text-align: left; margin-bottom: 5px; margin-left: 7px;">Admin Username</p>
                <input type="text" name="username" required>
                <p style="text-align: left; margin-bottom: 5px; margin-left: 7px;">Admin Password</p>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <label>
                        <input type="checkbox" id="showPassword">
                    </label>
                </div><br>
                <input type="submit" class="login" value="LOGIN" name="admin_login">
            </form>
            <p>Go to <button id="switchToEmployeeBtn">Employee Login</button></p>
            <button id="switchToForgotPasswordBtn">Forgot Password?</button>
        </div>

        <!-- Employee Login Form (Initially Hidden) -->
        <div id="employeeLoginForm" style="display: none;">
            <form method="POST">
            <p style="text-align: left; margin-bottom: 5px; margin-left: 7px;"><b>Employee ID</b></p>
                <input type="text" name="employee_id" required>
                <p style="text-align: left; margin-bottom: 5px; margin-left: 7px;"><b>Employee Password</b></p>

                <div class="password-container">
                    <input type="password" id="employeePassword" name="password" required>
                    <label>
                        <input type="checkbox" id="ishowPassword">
                    </label>
                </div>
                <input type="submit" class="login" value="LOGIN" name="employee_login">
            </form>
            <p>Switch back to <button id="switchToAdminBtn">Admin Login</button></p>
            <button id="switchToForgotPasswordFromEmployeeBtn">Forgot Password?</button>
        </div>
  

    <!-- Forgot Password Section (Initially Hidden) -->
    <div id="forgotPasswordSection" style="display: none;">

        <form method="POST">
            <select name="user_type" style="margin-bottom: 5px;" required>
                <option value="" disabled selected>Select User Role</option>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
            </select>
            <input type="text" name="username_or_employee_id" placeholder="Enter your Username or Employee ID" required>
            <input type="email" name="email" placeholder="Enter your email" required>
            <hr>
            <div class="password-container">
                <input type="password" id="newPassword" name="new_password" placeholder="New Password" required>
                <label>
                    <input type="checkbox" id="showNewPassword">
                </label>
            </div>
            <div class="password-container">
                <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                <label>
                    <input type="checkbox" id="showConfirmPassword">
                </label>
            </div>
            <input type="submit" class="resetPass" value="Reset Password" name="reset_password">
        </form>
        <p>Switch back to <button id="switchToAdminFromForgotPasswordBtn">Admin Login</button></p>
        </div>
    </div>

<script>


        // Switch between forms
        document.getElementById('switchToEmployeeBtn').addEventListener('click', function() {
            document.getElementById('adminLoginForm').style.display = 'none';
            document.getElementById('employeeLoginForm').style.display = 'block';
            document.getElementById('forgotPasswordSection').style.display = 'none';
            document.getElementById('loginHeader').textContent = 'Employee Login';
        });

        document.getElementById('switchToAdminBtn').addEventListener('click', function() {
            document.getElementById('adminLoginForm').style.display = 'block';
            document.getElementById('employeeLoginForm').style.display = 'none';
            document.getElementById('forgotPasswordSection').style.display = 'none';
            document.getElementById('loginHeader').textContent = 'Admin Login';
        });

        document.getElementById('switchToForgotPasswordBtn').addEventListener('click', function() {
            document.getElementById('adminLoginForm').style.display = 'none';
            document.getElementById('employeeLoginForm').style.display = 'none';
            document.getElementById('forgotPasswordSection').style.display = 'block';
            document.getElementById('loginHeader').textContent = 'Reset Password';
        });

        // Switch from Forgot Password to Admin Login
    document.getElementById('switchToAdminFromForgotPasswordBtn').addEventListener('click', function() {
    document.getElementById('adminLoginForm').style.display = 'block';
    document.getElementById('employeeLoginForm').style.display = 'none';
    document.getElementById('forgotPasswordSection').style.display = 'none';
    document.getElementById('loginHeader').textContent = 'Admin Login';
});


        // Switch from Forgot Password to Admin Login
        document.getElementById('switchToForgotPasswordFromEmployeeBtn').addEventListener('click', function() {
    document.getElementById('adminLoginForm').style.display = 'none';
    document.getElementById('employeeLoginForm').style.display = 'none';
    document.getElementById('forgotPasswordSection').style.display = 'block';
    document.getElementById('loginHeader').textContent = 'Reset Password';
});

// JavaScript to toggle between sections
const loginSection = document.getElementById('loginSection');
const forgotPasswordSection = document.getElementById('forgotPasswordSection');

document.getElementById('loginBtn').addEventListener('click', function() {
    loginSection.style.display = 'block';
    forgotPasswordSection.style.display = 'none';
});

document.getElementById('forgotPasswordBtn').addEventListener('click', function() {
    loginSection.style.display = 'none';
    forgotPasswordSection.style.display = 'block';
});



</script>
<script>
    // Toggle visibility for Employee Login password
document.getElementById('ishowPassword').addEventListener('change', function() {
    const employeePasswordField = document.getElementById('employeePassword');
    employeePasswordField.type = this.checked ? 'text' : 'password';
});

// Toggle visibility for Forgot Password section - New Password
document.getElementById('showNewPassword').addEventListener('change', function() {
    const newPasswordField = document.getElementById('newPassword');
    newPasswordField.type = this.checked ? 'text' : 'password';
});

// Toggle visibility for Forgot Password section - Confirm Password
document.getElementById('showConfirmPassword').addEventListener('change', function() {
    const confirmPasswordField = document.getElementById('confirmPassword');
    confirmPasswordField.type = this.checked ? 'text' : 'password';
});
</script>
<script src="js/no-previousbutton.js"></script>
<script src="js/Indexss.js"></script>

</body>
</html>
