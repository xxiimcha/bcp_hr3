<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="../../img/logo.webp">
    <title>Violations</title>
    <link rel="stylesheet" href="../../css/employee-violation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->
<?php include '../../partials/nav.php'; ?>
<!-- END OF TOP NAV BAR -->
<br>
<div class="form-container">
            <h2>Add New Violation Record</h2>
            <label for="employee-name">Employee Name:</label>
            <input type="text" id="employee-name" placeholder="Enter employee Name">
           

            <label for="violation-type">Violation Type:</label>
            <input type="text" id="violation-type" placeholder="Enter violation type">
        
            <label for="description">Description:</label>
            <textarea id="description" placeholder="Enter violation description"></textarea>

            <label for="date">Date:</label>
            <input type="date" id="violation-date">
            
            <button class="add-report-btn">Add Record</button>
        </div>

        <div class="table-container">
            <h2>Violation Records</h2>
            <table>
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Violation Type</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <!-- example violations -->
                <tbody id="violationsTableBody">
                    <tr>
                        <td>Johan Dale</td>
                        <td>Late arrival</td>
                        <td>Employee X arrived 30 minutes late to their shift.</td>
                        <td>2022-01-01</td>
                    </tr>
                    <!-- Add more rows here -->
                </tbody>
            </table>
        </div>
    </div>
<footer>
    <p>2024 Compliance</p>
</footer>

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

<script src="../../js/no-previousbutton.js"></script>
<script src="../../js/toggle-darkmode.js"></script>

</body>
<script>// main-sign-out.js
document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.getElementById('logout-button');
    const dialogOverlay = document.getElementById('dialog-overlay');
    const confirmButton = document.getElementById('confirm-button');
    const cancelButton = document.getElementById('cancel-button');

    // Show dialog on logout button click
    logoutButton.addEventListener('click', function () {
        dialogOverlay.style.display = 'flex'; // Display the dialog
    });

    // Handle confirm button click
    confirmButton.addEventListener('click', function () {
        // Logic for signing out (e.g., redirecting to a logout page)
        window.location.href = '../../log-out2.php'; // Example logout redirect
    });

    // Handle cancel button click
    cancelButton.addEventListener('click', function () {
        dialogOverlay.style.display = 'none'; // Hide the dialog
    });
});
</script>
</html>
