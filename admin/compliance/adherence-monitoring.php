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
    <title>Adherence Monitoring</title>
    <link rel="stylesheet" href="../../css/adherence_monitoring.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->
<?php include '../../partials/nav.php'; ?>
<!-- END OF TOP NAV BAR -->

    <div class="adherence-content">
    <h2>Adherence Monitoring</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Policy Name</th>
                    <th>Adherence Status</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Employee Conduct</td>
                    <td>Compliant</td>
                    <td>2024-10-01</td>
                </tr>
                <tr>
                    <td>Data Security</td>
                    <td>Non-Compliant</td>
                    <td>2024-09-28</td>
                </tr>
                <tr>
                    <td>Workplace Safety</td>
                    <td>Compliant</td>
                    <td>2024-10-05</td>
                </tr>
                <tr>
                    <td>Leave Policy</td>
                    <td>Pending Review</td>
                    <td>2024-09-30</td>
                </tr>
            </tbody>
        </table>
        <button class="add-report-btn">Add Report</button>
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
});</script>
<script src="../../js/no-previousbutton.js"></script>
<script src="../../js/toggle-darkmode.js"></script>
</body>

</html>
