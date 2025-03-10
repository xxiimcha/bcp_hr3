<?php
require '../config.php'; // Include the database connection

session_start();

$error = '';
$success_message = '';

// Handle admin login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $admin_username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Updated SQL query to match the admin_users table
    $sql = "SELECT * FROM admin_users WHERE admin_username=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $admin_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify the password using password_verify function
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $admin_username; // Store username in session
                $_SESSION['user_id'] = $row['id']; // Store user ID in session

                // Redirect based on the username
                if ($admin_username === 'QRscanner') {
                    header("Location: employee-clocking.php"); // Redirect for QRscanner user
                } else {
                    header("Location: time-and-attendance-home.php"); // Redirect to the default dashboard
                }
                exit();
            } else {
                $error = 'Invalid username or password.'; // Incorrect password
            }
        } else {
            $error = 'No username or password found.'; // No user found
        }

        $stmt->close();
    } else {
        $error = 'Database error: Unable to prepare statement.';
    }
}


// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $username_or_employee_id = isset($_POST['username_or_employee_id']) ? trim($_POST['username_or_employee_id']) : '';

    // Function to validate password complexity
    function isStrongPassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,}$/', $password);
    }

    // Check if passwords match and are strong
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!isStrongPassword($new_password)) {
        $error = "Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        if ($user_type === 'admin') {
            // Admin password reset
            $stmt = $conn->prepare("SELECT id FROM admin_users WHERE admin_username=? AND email=?");
            if ($stmt) {
                $stmt->bind_param("ss", $username_or_employee_id, $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->close();
                    $stmt = $conn->prepare("UPDATE admin_users SET password=? WHERE admin_username=? AND email=?");
                    if ($stmt) {
                        $stmt->bind_param("sss", $hashed_password, $username_or_employee_id, $email);
                        if ($stmt->execute()) {
                            $success_message = "Admin password successfully reset.";
                        } else {
                            $error = "Error updating admin password.";
                        }
                        $stmt->close();
                    } else {
                        $error = "Database error: Unable to prepare update statement for admin.";
                    }
                } else {
                    $error = "Admin username or email not found.";
                }
            } else {
                $error = "Database error: Unable to prepare select statement for admin.";
            }

        } elseif ($user_type === 'employee') {
            // Employee password reset
            // First, verify the employee_id exists and matches the email in employee_info
            $employee_id_int = intval($username_or_employee_id);
            $stmt = $conn->prepare("SELECT ei.employee_id FROM employee_info ei JOIN employee_logins el ON ei.employee_id = el.employee_id WHERE ei.employee_id=? AND ei.email_address=?");
            if ($stmt) {
                $stmt->bind_param("is", $employee_id_int, $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->close();
                    // Update the password in employee_logins
                    $stmt = $conn->prepare("UPDATE employee_logins SET password=? WHERE employee_id=?");
                    if ($stmt) {
                        $stmt->bind_param("si", $hashed_password, $employee_id_int);
                        if ($stmt->execute()) {
                            $success_message = "Employee password successfully reset.";
                        } else {
                            $error = "Error updating employee password.";
                        }
                        $stmt->close();
                    } else {
                        $error = "Database error: Unable to prepare update statement for employee.";
                    }
                } else {
                    $error = "Employee ID or email not found.";
                }
            } else {
                $error = "Database error: Unable to prepare select statement for employee.";
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
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/adminIndex.css">

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
            <img src="../img/logo.webp" class="plogo" alt="Paradise Hotel Logo">
        </div>



        <h1>This is Admin<h1>

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
            <button id="switchToForgotPasswordBtn">Forgot Password?</button>
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
