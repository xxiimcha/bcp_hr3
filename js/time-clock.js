

    function copyLinkToClipboard() {
        const link = 'http://localhost/CapsHumanResource/admin/employee-clocking.php'; // Replace with the actual full URL you want
        const tempInput = document.createElement('input');
        document.body.appendChild(tempInput);
        tempInput.value = link;
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        // Display the success message
        const successMessage = document.getElementById('copy-success-message');
        successMessage.style.display = 'block'; // Show the message
        successMessage.textContent = 'Link copied to clipboard!';
    
        // Optionally hide the message after a few seconds
        setTimeout(function() {
            successMessage.style.display = 'none';
        }, 3000); // Hide after 3 seconds
    }


        // Show the manual input overlay
        function showManualInputForm() {
            document.getElementById('manual-input-overlay').style.display = 'flex';
        }
    
        // Hide the manual input overlay
        function hideManualInputForm() {
            document.getElementById('manual-input-overlay').style.display = 'none';
        }
        function fetchEmployeeDetails() {
        const employeeIdInput = document.getElementById("employee-id-input").value;
        
        if (employeeIdInput === "") {
            alert("Please enter an Employee ID.");
            return;
        }
    
        // Make an AJAX request to fetch_employee.php
        fetch('fetch_employee_clocking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'employee_id=' + encodeURIComponent(employeeIdInput)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                document.getElementById("manual-employee-info").style.display = "none";
            } else {
                // Update the employee details in the overlay
                document.getElementById("display-employee-id").innerText = employeeIdInput; // Hide if not needed
                document.getElementById("manual-employee-name").innerText = data.employee_name; // Assuming the name will be fetched
                document.getElementById("manual-employee-position").innerText = data.position;
                document.getElementById("manual-employee-department").innerText = data.department_name;
                document.getElementById("manual-employee-info").style.display = "block";
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }