<?php
session_start();
session_destroy(); // Destroy all session data

// Redirect to the login page with a script to clear the history
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <script>
        // Redirect to the login page
        window.location.href = 'admin/index.php';
    </script>
</head>
<body>
</body>
</html>
