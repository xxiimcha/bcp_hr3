function fetchEmployeeDetails() {
    const employeeId = document.getElementById('employee_id').value;
    if (employeeId) {
        // Use AJAX to fetch employee details
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_employee_details.php?employee_id=' + employeeId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const employeeData = JSON.parse(xhr.responseText);
                document.getElementById('employee_name').value = employeeData.employee_name;
                document.getElementById('department').value = employeeData.department_name;
                document.getElementById('position').value = employeeData.position;
            }
        };
        xhr.send();
    } else {
        document.getElementById('employee_name').value = '';
        document.getElementById('department').value = '';
        document.getElementById('position').value = '';
    }
}

function calculateTotalDays() {
const startDate = new Date(document.getElementById('start_date').value);
const endDate = new Date(document.getElementById('end_date').value);

if (startDate && endDate) {
    const timeDiff = endDate - startDate; // Difference in milliseconds
    const totalDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Convert to days and add 1 for inclusivity

    if (totalDays > 0) {
        document.getElementById('total_days').value = totalDays;
    } else {
        document.getElementById('total_days').value = 0; // Reset to 0 if dates are invalid
    }
} else {
    document.getElementById('total_days').value = ''; // Reset if no dates are selected
}
}

// Add event listeners for date inputs
document.getElementById('start_date').addEventListener('change', calculateTotalDays);
document.getElementById('end_date').addEventListener('change', calculateTotalDays);



//EDIT LEAVE MODAL OVERLAY
// Get the modal
var modal = document.getElementById("editLeaveModal");

// Get the <span> element that closes the modal
var closeModal = document.getElementsByClassName("close")[0];

// Open the modal when the edit button is clicked
function openModal(leaveData) {
    modal.style.display = "block";

    // Populate readonly fields with selected leave data (non-editable)
    document.getElementById("edit_employee_name").value = leaveData.employee_name;
    document.getElementById("edit_department").value = leaveData.department;
    document.getElementById("edit_position").value = leaveData.position;

    // Populate editable fields
    document.getElementById("edit_leave_id").value = leaveData.leave_id;
    document.getElementById("edit_leave_type").value = leaveData.leave_type;
    document.getElementById("edit_start_date").value = leaveData.start_date;
    document.getElementById("edit_end_date").value = leaveData.end_date;
    document.getElementById("edit_remarks").value = leaveData.remarks;
    document.getElementById("edit_status").value = leaveData.status;
}

// Close the modal when the user clicks on <span> (x)
closeModal.onclick = function() {
    modal.style.display = "none";
}

// Close the modal if the user clicks anywhere outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
