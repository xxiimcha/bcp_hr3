<?php
require '../config.php'; // Include the database connection

$message = '';
$message_class = ''; // Added for message styling

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $checkUserSql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($checkUserSql);

    if ($result->num_rows > 0) {
        $message = "Username already exists. Please choose a different username.";
        $message_class = 'error'; // Class for error message
    } else {
        // Insert new user
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";

        if ($conn->query($sql) === TRUE) {
            $message = "Registration successful";
            $message_class = 'success'; // Class for success message
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
            $message_class = 'error'; // Class for error message
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
    <link rel="stylesheet" href="../css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Register</title>
</head>
<body>
    <div class="container">
    <button class="back-button" onclick="window.location.href='../index.php'">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Register">
        </form>
        <?php if ($message): ?>
            <p class="message <?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <p><a href="log-in.php">Already have an account? Login</a></p>
    </div>
</body>
<style>
    .container {
        position: relative;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        max-width: 400px;
        width: 100%;
        text-align: center;
    }

    .back-button {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 5px;
        padding-right: 10px;
        padding-left: 10px;
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 5px;
    }

    .back-button i {
        font-size: 18px;
    }

    .back-button:hover {
        background-color: #555;
    }
</style>

</style>
</html>
