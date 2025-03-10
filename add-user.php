<?php
include 'config.php'; // Include the database connection
session_start();

// Redirect to index if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$message = ""; // Initialize message variable
$admin_users = []; // Initialize array to hold admin users

// Fetch all admin users
$sql = "SELECT * FROM admin_users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admin_users[] = $row; // Add each user to the array
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_user'])) {
        // Handle user deletion
        $user_id = $_POST['user_id'];

        // Check if there is more than one user in the database
        $count_sql = "SELECT COUNT(*) AS total FROM admin_users";
        $count_result = $conn->query($count_sql);
        $count_row = $count_result->fetch_assoc();
        
        if ($count_row['total'] > 1) {
            // Delete the user from the database
            $delete_sql = "DELETE FROM admin_users WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);

            if ($delete_stmt->execute()) {
                $message = "<span style='color: #12E772;'>User deleted successfully.</span>";
                // Refresh admin users
                $result = $conn->query($sql);
                $admin_users = [];
                while ($row = $result->fetch_assoc()) {
                    $admin_users[] = $row; // Update the users array
                }
            } else {
                $message = "<span style='color: red;'>Error deleting user.</span>";
            }
        } else {
            $message = "<span style='color: red;'>Cannot delete the last remaining user.</span>";
        }
    } else {
        // Add new user functionality
        $new_username = $_POST['admin_username'];
        $new_email = $_POST['email']; // Get the email from the form
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if the username or email already exists
        $check_sql = "SELECT * FROM admin_users WHERE admin_username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $new_username, $new_email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "<span style='color: red;'>Username or email already exists. Please choose another one.</span>";
        } else {
            // Check if the new password and confirm password match
// Check if the new password and confirm password match
if ($new_password === $confirm_password) {
    // Validate the new password
    $password_pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

    if (preg_match($password_pattern, $new_password)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $insert_sql = "INSERT INTO admin_users (admin_username, password, email) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $new_username, $hashed_password, $new_email); // Bind email parameter

        if ($insert_stmt->execute()) {
            $message = "<span style='color: green;'>New user added successfully.</span>";
        } else {
            $message = "<span style='color: red;'>Error adding new user.</span>";
        }
    } else {
        $message = "<span style='color: red;'>Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.</span>";
    }
} else {
    $message = "<span style='color: red;'>New password and confirmation do not match.</span>";
}

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="img/logo.webp">

    <title>Add New User</title>
    <link rel="stylesheet" href="css/create-users.css">

    <style>
       
    </style>
</head>
<body>

<div class="container">
    <a href="manage_account.php" class="manage-admin-btn" id="manage-admin-btn">Manage Admin Accounts</a>
    <h2>Add New User</h2>

    <form method="POST" action="">
    <label for="admin_username">Admin Username</label>
    <input type="text" id="admin_username" name="admin_username" required>
    
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required placeholder="Enter email">

    <label for="password">Password</label>
    <div class="checkbox-container">
        <input type="password" id="password" name="password" placeholder="Enter password" required>
        <input type="checkbox" class="show-password" onclick="togglePasswordVisibility('password', this)">
    </div>
                                <!-- Password requirements note -->
                                <div class="password-requirements">
            <p style="color: #555; font-size: 0.9em; margin-bottom: 15px; margin-top: 5px;">
                New Password must meet the following criteria:<br>
                - At least one uppercase letter (A-Z)<br>
                - At least one lowercase letter (a-z)<br>
                - At least one number (0-9)<br>
                - At least one special character (e.g., !@#$%^&*)<br>
            </p>
    <label for="confirm_password">Confirm Password</label>
    <div class="checkbox-container">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
        <input type="checkbox" class="show-password" onclick="togglePasswordVisibility('confirm_password', this)">
    </div>

    <!-- Display message here -->
    <div class="message"><?php echo $message; ?></div>
    <button type="submit" class="update-btn">Add User</button>
</form>


    <a href="manage_account.php" class="back-btn">Back to Manage Account</a>
</div>

<!-- Overlay for displaying admin users -->
<div id="overlay" class="overlay">
    <div class="overlay-content">
        <span class="close-btn" onclick="closeOverlay()">&times;</span>
        <h2>Existing Admin Users</h2>
        <table class="admin-table">
    <thead>
        <tr>
            <th>Admin Username</th>
            <th>Email</th> <!-- Add email column -->
            <th>Actions</th> <!-- Add actions column -->
        </tr>
    </thead>
    <tbody>
        <?php foreach ($admin_users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['admin_username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td> <!-- Display email -->
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                        <button type="submit" name="delete_user" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, checkbox) {
        const inputField = document.getElementById(inputId);
        inputField.type = checkbox.checked ? 'text' : 'password';
    }

    // Function to open the overlay
    document.getElementById('manage-admin-btn').onclick = function(event) {
        event.preventDefault(); // Prevent the default anchor action
        document.getElementById('overlay').style.display = 'block';
    }

    // Function to close the overlay
    function closeOverlay() {
        document.getElementById('overlay').style.display = 'none';
    }

    // Close the overlay when clicking outside of the overlay content
    window.onclick = function(event) {
        if (event.target === document.getElementById('overlay')) {
            closeOverlay();
        }
    }
</script>
<script src="js/toggle-darkmode.js"></script>
</body>
<style>
</style>
</html>
