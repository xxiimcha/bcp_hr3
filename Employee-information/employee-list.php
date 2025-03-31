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
$api_url = "https://hr1.paradisehoteltomasmorato.com/api/all-employee-docs";
$employee_data = [];

$response = file_get_contents($api_url);
if ($response !== false) {
    $json_data = json_decode($response, true);
    if (isset($json_data['data'])) {
        $employee_data = $json_data['data'];
    }
}

$fingerprint_query = "SELECT employee_id FROM employee_fingerprints";
$fingerprint_result = $conn->query($fingerprint_query);

$fingerprint_ids = [];
if ($fingerprint_result && $fingerprint_result->num_rows > 0) {
    while ($row = $fingerprint_result->fetch_assoc()) {
        $fingerprint_ids[] = $row['employee_id'];
    }
}

?>
<?php include('../partials/navbar.php'); ?>
<style>
    .modal {
    display: none; /* Initially hidden */
    position: fixed;
    z-index: 1000;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 400px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
}

.scanner-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 20px 0;
}

.scanner-animation img {
    width: 120px;
    height: 120px;
}

.button-container {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.scan-btn, .submit-btn, .cancel {
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

.scan-btn {
    background: blue;
    color: white;
}

.submit-btn {
    background: green;
    color: white;
}

.cancel {
    background: red;
    color: white;
}

    </style>
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
        if (!empty($employee_data)) {
            foreach ($employee_data as $row) {
                $emp_id = htmlspecialchars($row['employee_no']);
                $full_name = htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
        
                echo "<tr>
                        <td>$emp_id</td>
                        <td>$full_name</td>
                        <td>" . htmlspecialchars($row['position']) . "</td>
                        <td>" . htmlspecialchars($row['position']) . "</td>
                        <td>" . htmlspecialchars(date('Y-m-d', strtotime($row['birthdate']))) . "</td>
                        <td>" . htmlspecialchars($row['number']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['address']) . "</td>
                        <td>" . htmlspecialchars($row['created_at']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                        <td>";
        
                // Check if employee_no exists in the fingerprints table
                if (in_array($row['employee_no'], $fingerprint_ids)) {
                    echo "<span style='color: green; font-weight: bold;'>✔ Enrolled</span>";
                } else {
                    echo "<button class='action-icon fingerprint-icon' onclick='openFingerprintModal(\"" . $emp_id . "\")'>
                            <i class='fas fa-fingerprint'></i> Enroll
                          </button>";
                }
                echo "<button class='action-icon view-icon' onclick='openViewModal(" . json_encode($row) . ")'>
                    <i class='fas fa-eye'></i> View
                </button>";

                echo "</td></tr>";
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

<!-- Fingerprint Enrollment Modal -->
<div id="fingerprintModal" class="modal">
    <div class="modal-content">
        <h3>Enroll Fingerprint</h3>
        <p>Place your finger on the scanner</p>

        <!-- Scanner Animation -->
        <div class="scanner-container">
            <div class="scanner-animation">
                <img id="fingerprint-image" src="../assets/fingerprint-static.png" alt="Fingerprint Scanner">
            </div>
        </div>

        <!-- Hidden Form for Fingerprint Enrollment -->
        <form id="enrollFingerprintForm" onsubmit="return false;">
            <input type="text" name="employee_id" id="modal_employee_id">
            <input type="hidden" name="fingerprint_data" id="fingerprint_data">

            <div class="button-container">
                <button type="button" class="scan-btn" onclick="startFingerprintScan()">Start Scan</button>
                <button type="button" class="cancel" onclick="closeFingerprintModal()">Cancel</button>
            </div>
        </form>

    </div>
</div>

<div id="viewEmployeeModal" class="modal" style="width:960px">
  <div class="modal-content" style="max-width: 960px; padding: 0;">
    <div style="background-color: #2e7d32; padding: 15px 25px; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="margin: 0;">Employee Information</h3>
      <button style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;" onclick="closeViewModal()">×</button>
    </div>

    <div style="padding: 25px; background-color: #f9f9f9; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
            <!-- Left Column -->
            <td style="width: 65%; vertical-align: top; padding-right: 30px; border-right: 1px solid #ccc;">
                <table style="width: 100%; font-size: 15px;">
                <tr><td><strong>Employee ID:</strong></td><td><span id="view-employee-id"></span></td></tr>
                <tr><td><strong>Birthdate:</strong></td><td><span id="view-birthdate"></span></td></tr>
                <tr><td><strong>Gender:</strong></td><td><span id="view-gender"></span></td></tr>
                <tr><td><strong>Civil Status:</strong></td><td><span id="view-civil-status"></span></td></tr>
                <tr><td><strong>Contact No:</strong></td><td><span id="view-contact"></span></td></tr>
                <tr><td><strong>Email:</strong></td><td><span id="view-email"></span></td></tr>
                <tr><td><strong>Address:</strong></td><td><span id="view-address"></span></td></tr>
                <tr><td><strong>Date Hired:</strong></td><td><span id="view-date-hired"></span></td></tr>
                <tr><td><strong>Status:</strong></td><td><span id="view-status"></span></td></tr>
                <tr><td><strong>SSS:</strong></td><td><span id="view-sss"></span></td></tr>
                <tr><td><strong>TIN:</strong></td><td><span id="view-tin"></span></td></tr>
                <tr><td><strong>PhilHealth:</strong></td><td><span id="view-philhealth"></span></td></tr>
                <tr><td><strong>Pag-IBIG:</strong></td><td><span id="view-pagibig"></span></td></tr>
                </table>
            </td>

            <!-- Right Column -->
            <td style="width: 35%; text-align: center; padding-left: 30px;">
                <img id="view-profile-img" src="" alt="Employee Photo" style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 4px solid #2e7d32; margin-bottom: 15px;">
                <h3 id="view-fullname" style="margin: 10px 0 5px 0;"></h3>
                <p id="view-position" style="color: #666; margin-bottom: 15px;"></p>
                <hr style="margin: 20px 0;">
                <p><strong>Organization:</strong> Paradise Hotel</p>
                <p><strong>Specialization:</strong> <span id="view-position-2"></span></p>
            </td>
            </tr>
        </table>
        </div>

  </div>
</div>


<!-- Include the CryptoJS library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>

<script>
function openViewModal(employee) {
    const profileImage = employee.profile?.startsWith("http") ? employee.profile : `../uploads/${employee.profile}`;
    const fullName = `${employee.firstname} ${employee.lastname}`;

    document.getElementById("view-profile-img").src = profileImage;
    document.getElementById("view-fullname").textContent = fullName;
    document.getElementById("view-position").textContent = employee.position;
    document.getElementById("view-position-2").textContent = employee.position;

    document.getElementById("view-employee-id").textContent = employee.employee_no;
    document.getElementById("view-birthdate").textContent = employee.birthdate;
    document.getElementById("view-gender").textContent = employee.gender;
    document.getElementById("view-civil-status").textContent = employee.civil_status;
    document.getElementById("view-contact").textContent = employee.number;
    document.getElementById("view-email").textContent = employee.email;
    document.getElementById("view-address").textContent = employee.address;
    document.getElementById("view-date-hired").textContent = employee.created_at;
    document.getElementById("view-status").textContent = employee.status;
    document.getElementById("view-sss").textContent = employee.sss || "—";
    document.getElementById("view-tin").textContent = employee.tin || "—";
    document.getElementById("view-philhealth").textContent = employee.philhealth || "—";
    document.getElementById("view-pagibig").textContent = employee.pagibig || "—";

    document.getElementById("viewEmployeeModal").style.display = "block";
}

function closeViewModal() {
    document.getElementById("viewEmployeeModal").style.display = "none";
}


function openFingerprintModal(employeeId) {
    console.log("Opening modal for Employee ID:", employeeId); // Debugging
    document.getElementById("modal_employee_id").value = employeeId;
    document.getElementById("fingerprintModal").style.display = "block";
    document.getElementById("fingerprint-image").src = "../assets/fingerprint-static.png";
}

function closeFingerprintModal() {
    document.getElementById("fingerprintModal").style.display = "none";
}

function startFingerprintScan() {
    var employeeId = document.getElementById("modal_employee_id").value.trim();

    if (!employeeId) {
        alert("Error: Employee ID is missing.");
        return;
    }

    console.log("Sending Employee ID:", employeeId); // Debugging log

    var fingerprintImage = document.getElementById("fingerprint-image");
    fingerprintImage.src = "../assets/fingerprint-scanning.gif"; // Show scanning animation

    $.ajax({
        url: "../api/enroll_fingerprint.php",
        type: "POST",
        data: { 
            employee_id: employeeId, 
            action: "scan" 
        },
        success: function (response) {
            console.log("Server response:", response);

            if (response.includes("already enrolled")) {
                fingerprintImage.src = "../assets/fingerprint-fail.png";
                alert("Fingerprint is already enrolled for this employee.");
            } else if (response.includes("success")) {
                fingerprintImage.src = "../assets/fingerprint-success.png";
                alert("Fingerprint scan successful.");
            } else {
                fingerprintImage.src = "../assets/fingerprint-error.png";
                alert("Fingerprint scan failed. Try again.");
            }
        },
        error: function () {
            fingerprintImage.src = "../assets/fingerprint-error.png";
            alert("Error connecting to fingerprint scanner.");
        }
    });
}

</script>

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


<script src="../js/sign_out.js"></script>
<script src="../jsno-previousbutton.js"></script>
<script src="../js/toggle-darkmode.js"></script>
<script src="../js/employee-list.js"></script>
</body>
<style>
</style>
</html>
