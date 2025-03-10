<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch leave balances for all employees
$query = "
    SELECT e.employee_id, e.employee_name, lb.leave_code, lb.balance 
    FROM employee_leave_balances lb
    JOIN employee_info e ON lb.employee_id = e.employee_id
";
$result = $conn->query($query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balances</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/leaverecord.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="light-mode"> <!-- Initially setting light mode -->

    <main>
        <center>
            <h1>Leave Balances</h1>
            <div class="search-container">
            Search: <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Find...">
            </div>
            <!-- Leave Balances Table -->
            <table class="employee-table" id="leaveBalancesTable">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Leave Code</th>
                        <th>Leave Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['leave_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['balance']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No leave balance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </center>
    </main>

    <footer>
        <p>2024 Leave Management</p>
    </footer>

</body>
<style>
    /* Add any additional styles here */
</style>
<script src="../js/toggle-darkmode.js"></script>

<script>
function searchTable() {
    // Get the input value and convert it to lowercase
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("leaveBalancesTable");
    const tr = table.getElementsByTagName("tr");

    // Loop through all table rows (except the first, which contains table headers)
    for (let i = 1; i < tr.length; i++) {
        const tdEmployeeID = tr[i].getElementsByTagName("td")[0];
        const tdEmployeeName = tr[i].getElementsByTagName("td")[1];

        if (tdEmployeeID || tdEmployeeName) {
            const txtValueID = tdEmployeeID.textContent || tdEmployeeID.innerText;
            const txtValueName = tdEmployeeName.textContent || tdEmployeeName.innerText;

            // Check if the input matches either the Employee ID or Employee Name
            if (txtValueID.toLowerCase().indexOf(filter) > -1 || txtValueName.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = ""; // Show the row
            } else {
                tr[i].style.display = "none"; // Hide the row
            }
        }
    }
}
</script>
