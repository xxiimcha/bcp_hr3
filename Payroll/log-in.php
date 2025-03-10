<?php
session_start();
require '../config.php'; // Include the database connection

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $row['id']; // Store user ID in session
            header("Location: payroll-processing.php");
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/log-in.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Login</title>
</head>

<body>
    <div class="container">
        <!-- Back button at the top-left corner inside the container -->
        <button class="back-button" onclick="window.location.href='../index.php'">
            <i class="fas fa-arrow-left"></i>
        </button>

        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <p><a href="register.php">Don't have an account? Register</a></p>
    </div>

    <!-- Popup for Error Message -->
    <?php if ($error): ?>
    <div id="popup" class="popup show">
        <div class="popup-content">
            <p><?php echo $error; ?></p>
            <center><button class="close" id="closePopupButton">Close</button></center>
        </div>
    </div>
    <?php endif; ?>

    <script src="js/log-in.js"></script>
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
        background-color: #fff;
        color: #555;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 5px;
    }

    .back-button i {
        font-size: 18px;
    }


</style>

</style>
</html>
