<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch employee data from API
$employee_data = [];
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";

$response = file_get_contents($api_url);
if ($response !== false) {
    $decoded = json_decode($response, true);
    if (isset($decoded['data'])) {
        $employee_data = $decoded['data'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Record</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/leaverecord.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->
<?php include '../partials/nav.php'; ?>
<!-- END OF TOP NAV BAR -->
    
<main>
    <center>
        <div class="search-container">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for employee info..">
        </div>

        <table class="employee-table" id="employeeTable">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Leave Records</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($employee_data)): ?>
                    <?php foreach ($employee_data as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['employee_no']) ?></td>
                            <td><?= htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) ?></td>
                            <td><?= htmlspecialchars($employee['position']) ?></td>
                            <td><?= htmlspecialchars($employee['position']) ?></td>
                            <td>
                                <a href="manage-leave.php?employee_id=<?= htmlspecialchars($employee['employee_no']) ?>" title="View Leave Records">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No employee records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </center>
</main>

<footer>
    <p>2024 Leave Management</p>
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
    
    <script src="../js/no-previousbutton.js"></script>
    <script src="../js/sign_out.js"></script>
    <script src="../js/toggle-darkmode.js"></script>

    <!-- JavaScript for Search Bar -->
    <script>
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("employeeTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }
    </script>

</body>
<style>
    .view-leave-balances {
        margin-bottom: 20px; /* Add some space below the button */
    }
    .button {
        padding: 10px 15px;
        background-color: #555; /* Background color for the button */
        color: white; /* Text color */
        border: none; /* No border */
        border-radius: 5px; /* Rounded corners */
        text-decoration: none; /* Remove underline */
        font-size: 16px; /* Font size */
    }
    .button:hover {
        background-color: #777; /* Darker shade on hover */
    }
</style>
</html>

