<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Time Clocking</title>
    <link rel="icon" type="image/webp" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/qr-scanner2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
        <!--THIS IS FOR INTERNAL OR OFFLINE QRSCANNER I IMPORT QRCODE<script src="./node_modules/html5-qrcode/html5-qrcode.min.js"></script>-->

<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
</head>
<style>



</style>
<body>
             
    <!-- Panel with Employee Information -->
    <div class="container">
        <div id="header">

          <!-- Logout Button -->
    <button id="logout-button" class="logout-button">
        <i class="fas fa-sign-out-alt"></i> Logout
    </button>
</div>

<!-- Logout Confirmation Dialog -->
<div id="dialog-overlay" class="dialog-overlay" style="display: none;">
    <div class="dialog-box">
        <p>Are you sure you want to log out?</p>
        <div class="dialog-buttons">
            <button id="confirm-button" class="confirm-button">Yes</button>
            <button id="cancel-button" class="cancel-button">No</button>
        </div>
    </div>              




        </div>

        <div class="info-panel">
            <h2>Scan QR CODE</h2><br>
            <hr class="hr"></hr><br>
            <h4>Stamp the attendance and ensure your working hours are accurately recorded.</h4><br>

            <!-- QR Code Scanner -->
            <center>
                <div class="scanner-container">
                    <div id="qr-reader"></div> <!-- QR Scanner will appear here -->
                    <div id="qr-reader-results"></div> <!-- Scan results will be displayed here -->
                    <!-- Success Message Container -->
            <div id="success-message" style="display: none; margin-top: 20px; color: #fff; font-weight: bold;"></div>
                </div>
            </center>
            
                         <!-- Add Manual Input Button
<button class="manual-input-button" onclick="showManualInputForm()">Manual Input</button><br> -->

<p><strong class="p-text">Employee ID:</strong> <span class="text" id="employee-id"></span></p>
<p><strong class="p-text">Employee Name:</strong> <span class="text" id="employee-name"></span></p>
<p><strong class="p-text">Position:</strong> <span class="text" id="employee-position"></span></p>
<p><strong class="p-text">Department:</strong> <span class="text" id="employee-department"></span></p>
<hr>
<p><strong class="p-text">Shift Type:</strong> <span class="text" id="shift-name"></span></p>
<p><strong class="p-text">Shift Start:</strong> <span class="text" id="shift-start"></span></p>
<p><strong class="p-text">Shift End:</strong> <span class="text" id="shift-end"></span></p>
<div class="button-panel">
                <button class="time-button" id="time-in-button">Time In</button>
                <button class="time-button" id="time-out-button">Time Out</button>
                <button class="time-button" id="overtime-in-button">Overtime In</button>
                <button class="time-button" id="overtime-out-button">Overtime Out</button>
            </div>
        </div>
    </div>
    </div>


<!--THIS PART IS FOR MANUAL-->



<script>
  //SCAN QRCODE

      // Function to decrypt the data
      function decryptData(encryptedText) {
        var bytes = CryptoJS.AES.decrypt(encryptedText, '4S2aR9xB8pLmEoD1K3PqV7wXcAeJiG6');
        var decryptedData = bytes.toString(CryptoJS.enc.Utf8);
        return decryptedData;
    }

    // Function to initialize the QR code scanner
    function initializeQrScanner() {
        var qrCodeScanner = new Html5Qrcode("qr-reader");
        qrCodeScanner.start(
            { facingMode: "environment" }, 
            { fps: 10, qrbox: 250 }, 
            onScanSuccess, 
            onScanError
        );
    }

    // Function to reset employee data display
    function resetEmployeeDataDisplay() {
        document.getElementById('employee-name').innerText = '';
        document.getElementById('employee-position').innerText = '';
        document.getElementById('employee-department').innerText = '';
        document.getElementById('success-message').style.display = "none"; // Hide success message
    }

    // Function to handle successful scans
    function onScanSuccess(qrCodeMessage) {
        // Assume qrCodeMessage is the encrypted employee ID
        var encryptedId = qrCodeMessage.trim();
        var employeeId = decryptData(encryptedId); // Decrypt the scanned employee ID

        // Display the scanned employee ID
        document.getElementById('qr-reader-results').innerText = `Scanning Complete!
         
        Employee ID: ${employeeId}`;
        document.getElementById('employee-id').innerText = employeeId;

        // Clear previous employee data
        resetEmployeeDataDisplay();

        // Fetch employee data from PHP
        fetchEmployeeData(employeeId);

        // Disable scanner for 3 seconds
        disableScanner();
    }

    function disableScanner() {
        // Stop the scanner
        var qrCodeScanner = new Html5Qrcode("qr-reader");
        qrCodeScanner.stop().then((ignore) => {
            // Restart the scanner after 3 seconds
            setTimeout(() => {
                initializeQrScanner();
            }, 3000); // 3000 milliseconds = 3 seconds
        }).catch((err) => {
            console.error("Failed to stop QR scanner: ", err);
        });
    }

    function onScanError(errorMessage) {
        // Handle the error
        console.error(errorMessage);
    }

    function fetchEmployeeData(employeeId) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', `get_employee_info.php?employee_id=${employeeId}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var employeeData = JSON.parse(xhr.responseText);

            if (employeeData) {
                document.getElementById('employee-name').innerText = employeeData.employee_name;
                document.getElementById('employee-position').innerText = employeeData.position;
                document.getElementById('employee-department').innerText = employeeData.department_name;
                
                // Display shift details
                document.getElementById('shift-name').innerText = employeeData.shift_name || "N/A";
                document.getElementById('shift-start').innerText = employeeData.shift_start || "N/A";
                document.getElementById('shift-end').innerText = employeeData.shift_end || "N/A";

                document.getElementById('success-message').innerText = "Employee data loaded successfully.";
                document.getElementById('success-message').style.display = "block";
            } else {
                resetEmployeeDataDisplay();
                document.getElementById('success-message').innerText = "Employee not found.";
                document.getElementById('success-message').style.display = "block";
            }
        } else {
            resetEmployeeDataDisplay();
            document.getElementById('success-message').innerText = "Error fetching employee data.";
            document.getElementById('success-message').style.display = "block";
            console.error("Error fetching employee data");
        }
    };
    xhr.send();
}


    // Initialize the QR scanner on page load
    initializeQrScanner();

</script>

<script>
    // Time In functionality
    document.getElementById('time-in-button').addEventListener('click', function () {
        var employeeId = document.getElementById('employee-id').innerText;

        if (!employeeId) {
            alert("Employee ID not found. Please scan the QR code first.");
            return;
        }

        // Send AJAX request to time in the employee
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../admin/clocking/time_in.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    alert(response.message);
                } else {
                    alert(response.message);
                }
            } else {
                console.error("Failed to time in the employee.");
            }
        };
        xhr.send(`employee_id=${employeeId}`);
    });

    // Time Out functionality
    document.getElementById('time-out-button').addEventListener('click', function () {
        var employeeId = document.getElementById('employee-id').innerText;

        if (!employeeId) {
            alert("Employee ID not found. Please scan the QR code first.");
            return;
        }

        // Send AJAX request to time out the employee
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../admin/clocking/time_out.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    alert(response.message);
                } else {
                    alert(response.message);
                }
            } else {
                console.error("Failed to time out the employee.");
            }
        };
        xhr.send(`employee_id=${employeeId}`);
    });


    // Overtime In functionality
document.getElementById('overtime-in-button').addEventListener('click', function () {
    var employeeId = document.getElementById('employee-id').innerText;

    if (!employeeId) {
        alert("Employee ID not found. Please scan the QR code first.");
        return;
    }

    // Send AJAX request to register Overtime In
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../admin/clocking/overtime_in.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                alert(response.message);
            } else {
                alert(response.message);
            }
        } else {
            console.error("Failed to register overtime in for the employee.");
        }
    };
    xhr.send(`employee_id=${employeeId}`);
});
// Overtime Out functionality
document.getElementById('overtime-out-button').addEventListener('click', function () {
    var employeeId = document.getElementById('employee-id').innerText;

    if (!employeeId) {
        alert("Employee ID not found. Please scan the QR code first.");
        return;
    }

    // Send AJAX request to register Overtime Out
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../admin/clocking/overtime_out.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                alert(response.message);
            } else {
                alert(response.message);
            }
        } else {
            console.error("Failed to register overtime out for the employee.");
        }
    };
    xhr.send(`employee_id=${employeeId}`);
});

</script>

<script>
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
        window.location.href = '../log-out2.php'; // Example logout redirect
    });

    // Handle cancel button click
    cancelButton.addEventListener('click', function () {
        dialogOverlay.style.display = 'none'; // Hide the dialog
    });
});

</script>

</body>
    <!-- JavaScript to handle overlays and confirmations -->
    <script src="../js/no-previousbutton.js"></script>
    <script src="../js/toggle-darkmode.js"></script>
    <script src="../js/time-clock.js"></script>

</html>
