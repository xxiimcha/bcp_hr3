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
    <link rel="stylesheet" href="../../css/labor_policies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->
<?php include '../../partials/nav.php'; ?>
<!-- END OF TOP NAV BAR -->

<!-- Labor Policy table -->
<div class="table-container">
            <h2>Labor Policies</h2><br>
            <table>
                <thead>
                    <tr>
                        <th>Policy Name</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Upcoming Changes</th>
                    </tr>
                </thead>
                <tbody id="laborPoliciesTableBody">
                </tbody>
            <tbody>
                <tr>
                    <td>Attendance Policy</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>July 15, 2023</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Overtime Rules</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>September 1, 2023</td>
                    <td><span class="upcoming-changes"><i class="fas fa-exclamation-circle"></i> Changes pending from 2024 Labor Law</span></td>
                </tr>
                <tr>
                    <td>Leave Policy</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>January 10, 2022</td>
                    <td><span class="upcoming-changes"><i class="fas fa-exclamation-circle"></i> Major updates coming by Q4 2024</span></td>
                </tr>
                <tr>
                    <td>Health and Safety Regulations</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>March 25, 2023</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Anti-Harassment Policy</td>
                    <td><select class="status-dropdown outdated">
                            <option value="active">Active</option>
                            <option value="review">Under Review</option>
                            <option value="outdated" selected>Outdated</option>
                        </select></td>
                    <td>June 12, 2023</td>
                    <td><span class="upcoming-changes"><i class="fas fa-exclamation-circle"></i> New training requirements in 2024</span></td>
                </tr>
            </tbody>
        </table>
    </div>

<footer>
    <p>2024 Labor Policies</p>
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
