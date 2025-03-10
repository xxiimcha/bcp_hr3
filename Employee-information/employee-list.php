<?php

include '../config.php';

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch employee data
// Populate the dropdown with employee names and IDs

// Fetch employee data along with department names
$sql = "SELECT employee_info.*, departments.department_name 
        FROM employee_info 
        JOIN departments ON employee_info.department_id = departments.department_id";
$result = $conn->query($sql);

// Fetch departments from the database
$departments = [];
$department_sql = "SELECT department_name FROM departments"; // Adjust this query based on your actual table structure
$department_result = $conn->query($department_sql);

if ($department_result->num_rows > 0) {
    while ($row = $department_result->fetch_assoc()) {
        $departments[] = $row['department_name'];
    }
}


// Handle edit request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['employee_id'])) {
    $employee_id = intval($_POST['employee_id']); // Ensure this is an integer
    $employee_name = $_POST['employee_name'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $date_of_birth = $_POST['date_of_birth'];
    $contact_no = $_POST['contact_no'];
    $email_address = $_POST['email_address'];
    $address = $_POST['address'];
    $date_hired = $_POST['date_hired'];
    $status = $_POST['status'];
 
    // Update employee_info table
    $update_sql = "UPDATE employee_info 
    SET employee_name = ?, 
        department_id = (SELECT department_id FROM departments WHERE department_name = ?), 
        position = ?, 
        date_of_birth = ?, 
        contact_no = ?, 
        email_address = ?, 
        address = ?, 
        date_hired = ?, 
        status = ? 
    WHERE employee_id = ?";
$stmt = $conn->prepare($update_sql);

// Corrected type definition to "sssssssssi" (9 s + 1 i)
$stmt->bind_param("sssssssssi", $employee_name, $department, $position, $date_of_birth, $contact_no, $email_address, $address, $date_hired, $status, $employee_id);


    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Employee updated successfully!";
    } else {
        $_SESSION['success_message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Ensure no further code is executed after the redirect
}


// Handle delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $employee_id = intval($_POST['delete_id']); // Ensure this is an integer

    // Delete from employee_info table
    $delete_sql = "DELETE FROM employee_info WHERE employee_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $employee_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Employee deleted successfully!";
    } else {
        $_SESSION['success_message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    
    // Redirect to the same page to refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Ensure no further code is executed after the redirect
}

$employee_sql = "SELECT employee_id, employee_name FROM employee_info";
$employee_result = $conn->query($employee_sql);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Timesheet</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/employee-list.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<!-- Include jQuery and QR Code Library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>


</head>


<body class="light-mode"> <!-- Initially setting light mode -->
<div class="top-nav">
        <ul>
        <a href="../maindashboard.php">
            <h1 class="logopos">
                Paradise <br> Hotel
            </h1>
        </a>
            <li class="top">
                <a class="top1" href="">
                <i class="fas fa-compass"></i> <!-- Icon for Compass -->
                    Navigate
                </a>
                <div class="top1dropdown">
                    <div class="dropdown-column">
                        <h3>Payroll</h3>
                        <a href="../admin/time-and-attendance-home.php">
                            <i class="fas fa-clock"></i> Time and Attendance <!-- Icon for Time and Attendance -->
                        </a>
                        <a href="../Employee-information/employee-list.php">
                            <i class="fas fa-users"></i> Employee Information <!-- Icon for Employee Information -->
                        </a>
                        <a href="payroll/log-in.php">
                            <i class="fas fa-calculator"></i> Payroll Processing <!-- Icon for Payroll Processing -->
                        </a>
                    </div>           
                </div>
            </li>
            <li class="top">
                <a class="top1" href="">
                    <i class="fas fa-user-cog"></i> <!-- Icon for Manage Employee -->
                    Manage Employee
                </a>
                <div class="top1dropdown">
                    <div class="dropdown-column">
                        <h3><b>Manage Employee</b></h3>
                        <a href="employee-form.php">
                            <i class="fas fa-user-plus"></i> Employee Form <!-- Icon for Employee Form -->
                        </a>
                        <a href="employee-list.php">
                            <i class="fas fa-list-ul"></i> Employee List <!-- Icon for Employee List -->
                        </a>
                    </div>
                    <div class="dropdown-column">
                        <h3><b>Employee Portal</b></h3>
                        <a href="employee_accounts.php">
                            <i class="fas fa-id-badge"></i> Employee Accounts <!-- Icon for Employee Accounts -->
                        </a>
                    </div>
                    <div class="dropdown-column">
                        <h3><b>Department</b></h3>
                        <a href="department.php">
                            <i class="fas fa-building"></i> Manage Department <!-- Icon for Manage Department -->
                        </a>
                    </div>
                </div>
            </li>
            <li class="top">
                <a class="top1" href="#settings">
                    <i class="fas fa-cog"></i> <!-- Icon for Settings -->
                    Settings
                </a>

            </li>
        </ul>
         <!-- <button type="button" id="darkModeToggle" class="dark-mode-toggle">Dark Mode</button> -->
         <button type="button" id="darkModeToggle" class="dark-mode-toggle" aria-label="Toggle Dark Mode">
            <i class="fas fa-moon"></i> <!-- Example icon for dark mode -->
         </button>

        <!-- USER  -->
        <div class="admin-section">
            <div class="admin-name">
                User - <?php echo htmlspecialchars($username); ?>
                <div class="admin-dropdown-content">
                    <a href="../manage_account.php">
                    Manage Account <!-- Icon for Manage Account -->
                    </a>
                </div>
            </div>
        </div>
        <button type="button" class="logout" id="logout-button">
            <i class="fas fa-sign-out-alt"></i> <!-- Icon for Logout -->
        </button>
        <!-- END OF TOP NAVIGATIONAL BAR -->
    </div>




<!-- Employee List Table -->
<div class="employee-list-container"><br>
    <h2>Employee List</h2>
    <div class="search-sort-container">
    <div class="sort-by">
        <label for="sort">Sort by:</label>
        <select id="sort" onchange="sortTable()">
            <option value="id">Employee ID</option>
            <option value="name_asc">Employee Name A-Z</option>
            <option value="name_desc">Employee Name Z-A</option>
        </select>
    </div>
    <!-- CREATE HERE A SELECT EMPLOYEE DROPDOWN AND BUTTON TO GENERATE QRCODE-->
 

    <div class="search-bar">
        <input type="text" id="search" placeholder="Search..." onkeyup="searchTable()">
    </div>
</div>

    <table class="employee-table" id="employeeTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Date of Birth</th>
                <th>Contact No.</th>
                <th>Email Address</th>
                <th>Address</th>
                <th>Date Hired</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php
    // Check if there are results and display them
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['employee_id']) . "</td>
                    <td>" . htmlspecialchars($row['employee_name']) . "</td>
                    <td>" . htmlspecialchars($row['department_name']) . "</td> <!-- Displaying department_name instead of department -->
                    <td>" . htmlspecialchars($row['position']) . "</td>
                    <td>" . htmlspecialchars($row['date_of_birth']) . "</td>
                    <td>" . htmlspecialchars($row['contact_no']) . "</td>
                    <td>" . htmlspecialchars($row['email_address']) . "</td>
                    <td>" . htmlspecialchars($row['address']) . "</td>
                    <td>" . htmlspecialchars($row['date_hired']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                    <td>
                        <button class='action-icon edit-icon' onclick='openEditOverlay(" . htmlspecialchars($row['employee_id']) . ", \"" . htmlspecialchars($row['employee_name']) . "\", \"" . htmlspecialchars($row['department_name']) . "\", \"" . htmlspecialchars($row['position']) . "\", \"" . htmlspecialchars($row['date_of_birth']) . "\", \"" . htmlspecialchars($row['contact_no']) . "\", \"" . htmlspecialchars($row['email_address']) . "\", \"" . htmlspecialchars($row['address']) . "\", \"" . htmlspecialchars($row['date_hired']) . "\", \"" . htmlspecialchars($row['status']) . "\")'><i class='fas fa-edit'></i></button>
                        <button class='action-icon delete-icon' onclick='openDeleteConfirmation(" . htmlspecialchars($row['employee_id']) . ", \"" . htmlspecialchars($row['employee_name']) . "\")'><i class='fas fa-trash'></i></button>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='11'>No employees found.</td></tr>";
    }
    ?>
</tbody>

    </table>
    
    <?php
    // Display the message below the table
    if (isset($_SESSION['success_message'])) {
        echo "<div class='message'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
        unset($_SESSION['success_message']); // Clear the message after displaying
    }
    ?>


    
<center>
    <!-- Container for dropdown and button -->
    <div class="dropdown-container">
        <!-- Dropdown to select employee -->
        <div class="employee-dropdown">
            <select id="employee_select" onchange="updateEmployeeName()">
                <option value="" disabled selected>Select an employee</option>
                <?php
                if ($employee_result->num_rows > 0) {
                    while ($employee_row = $employee_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($employee_row['employee_id']) . "' data-name='" . htmlspecialchars($employee_row['employee_name']) . "'>" . htmlspecialchars($employee_row['employee_name']) . "</option>";
                    }
                }
                ?>
            </select>
            <!-- Button to generate QR code -->
            <button id="generate_qr" class="generate_qr" onclick="generateQRCode()">Generate QR Code</button>
            <button class="print-button" onclick="printEmployeeDetails()">Save File</button>
            
        </div>
    </div>
</center>
<center>
    <!-- Container to display the QR Code -->
    <div id="qrcode" style="display: none; margin-top: 20px; margin-bottom: 20px;"></div>
    <img id="qr_image" style="display: none; padding: 30px 30px; margin-top: 20px;" alt="QR Code Image" />
</center>

<!-- Include the CryptoJS library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>

<script>
// Function to update employee name when dropdown changes
function updateEmployeeName() {
    var selectedOption = document.getElementById("employee_select").selectedOptions[0];
    var employeeName = selectedOption ? selectedOption.dataset.name : '';
    return employeeName;
}

// QR Code Generation Function
function generateQRCode() {
    var employeeId = document.getElementById("employee_select").value; // Get selected employee ID
    $("#qr_image").empty(); // Clear previous QR code

    if (employeeId) {
        // Encrypt only the employee ID
        var encryptedId = CryptoJS.AES.encrypt(employeeId, '4S2aR9xB8pLmEoD1K3PqV7wXcAeJiG6').toString();

        // Generate QR Code and get its data URL
        $("#qr_image").qrcode({
            text: encryptedId, // Use only the encrypted employee ID
            width: 250, // Size of the QR code
            height: 250,
            render: 'canvas', // Render as canvas
            background: "#ffffff",
            foreground: "#000000"
        });

        // Wait for the QR code to be generated
        setTimeout(function() {
            var canvas = $("#qr_image canvas")[0]; // Get the canvas element
            if (canvas) {
                var dataURL = canvas.toDataURL("image/png"); // Convert canvas to image data URL
                $("#qr_image").attr("src", dataURL).show(); // Set the image src and display it
            }
        }, 500); // Adjust timeout as needed to ensure QR code is generated
    }
}

// Print function for employee details
function printEmployeeDetails() {
    var employeeId = document.getElementById("employee_select").value; // Get selected employee ID
    var selectedOption = document.getElementById("employee_select").selectedOptions[0];
    var employeeName = selectedOption ? selectedOption.dataset.name : '';

    var qrImageSrc = $("#qr_image").attr("src"); // Get QR code image source

    // Create a new window for printing
    var printWindow = window.open('', '_blank');
    printWindow.document.write(`
 <html>
    <head>
        <title>Paradise Hotel</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f0f0f0;
            }
            .container { 
                width: 300px; /* ID card width */
                height: 450px; /* ID card height */
                border: 2px solid black; /* Border for the card */
                border-radius: 15px; /* Rounded corners */
                padding: 10px;
                background: white;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                text-align: center;
            }
            h1 {
                font-size: 18px; /* Smaller title size */
                margin-bottom: 10px;
            }
            .details {
                margin-bottom: 15px;
                font-size: 14px; /* Adjust font size for details */
            }
            img {
                max-width: 250px; /* Smaller image size */
                margin: auto;
            }
        </style>
    </head>
    <body>
        <center>
            <div class="container">
                <h1>Paradise Hotel</h1>
                <div class="details">
                    <strong>QR Code:</strong><br><br>
                    <img src="${qrImageSrc}" alt="QR Code">
                </div>
                <div class="details">
                    <p><strong>Employee ID:</strong> ${employeeId}</p>
                    <p><strong>Employee Name:</strong> ${employeeName}</p>
                </div>
            </div>
        </center>
    </body>
</html>

    `);
    printWindow.document.close(); // Close the document for writing
    printWindow.print(); // Trigger the print dialog
    printWindow.close(); // Close the print window after printing
}
</script>


<script>
    
</script>








<!-- Edit Employee Overlay -->
<div id="edit-overlay" class="dialog-overlay">
    <div class="edialog-content">
        <h3>Edit Employee</h3>
        <form id="edit-form" method="POST" action="">
            <input type="hidden" name="employee_id" id="employee_id" value="">
            
            <div class="form-group">
                <label for="employee_name">Employee Name:</label>
                <input type="text" name="employee_name" id="employee_name" required class="form-input">
            </div>
            
            <div class="form-group">
                <label for="department">Department:</label>
                <select name="department" id="department" required class="form-input">
                    <option value="" disabled>Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="position">Position:</label>
                <input type="text" name="position" id="position" required class="form-input">
            </div>

            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" name="date_of_birth" id="date_of_birth" required class="form-input">
            </div>

            <div class="form-group">
                <label for="contact_no">Contact No:</label>
                <input type="text" name="contact_no" id="contact_no" required class="form-input">
            </div>

            <div class="form-group">
                <label for="email_address">Email Address:</label>
                <input type="email" name="email_address" id="email_address" required class="form-input">
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" name="address" id="address" required class="form-input">
            </div>

            <div class="form-group">
                <label for="date_hired">Date Hired:</label>
                <input type="date" name="date_hired" id="date_hired" required class="form-input">
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <input type="text" name="status" id="status" required class="form-input">
            </div>

            <div class="button-container">
                <button type="submit" class="submit-btn">Save Changes</button>
                <button type="button" class="cancel" onclick="closeEditOverlay()">Cancel</button>
            </div>
        </form>
    </div>
</div>


<footer>
    <p>2024 Employee Information</p>

</footer>

<!-- Custom Confirmation Dialog -->
<div id="dialog-overlay" class="sdialog-overlay">
        <div class="sdialog-content">
            <h3>Are you sure you want to sign out?</h3>
            <div class="dialog-buttons">
                <button id="confirm-button">Sign Out</button>
                <button class="cancel" id="cancel-button">Cancel</button>
            </div>
        </div>
    </div>   
<!-- Delete Confirmation Dialog -->
<div id="delete-confirmation-dialog" class="dialog-overlay1">
    <div class="dialog-content1">
        <h3>Are you sure you want to permanently delete<br> <span id="employee-name-to-delete"></span> information?</h3>
        <form id="delete-form" method="POST" action="">
            <input type="hidden" name="delete_id" id="delete-id" value="">
            <div class="button-container">
    <button type="submit" class="submit-btn">Yes</button>
    <button type="button" class="cancel" onclick="closeDeleteConfirmation()">No</button>
</div>

        </form>
    </div>
</div>





<script src="../js/sign_out.js"></script>
<script src="../jsno-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
<script src="../js/employee-list.js"></script>
</body>
<style>
</style>
</html>
