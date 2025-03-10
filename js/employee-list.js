function openEditOverlay(employee_id, name, department, position, dob, contact, email, address, dateHired, status) {
    document.getElementById('employee_id').value = employee_id;
    document.getElementById('employee_name').value = name;
    document.getElementById('department').value = department; // Set the selected department
    document.getElementById('position').value = position;
    document.getElementById('date_of_birth').value = dob;
    document.getElementById('contact_no').value = contact;
    document.getElementById('email_address').value = email;
    document.getElementById('address').value = address;
    document.getElementById('date_hired').value = dateHired;
    document.getElementById('status').value = status;

    document.getElementById('edit-overlay').style.display = 'block';
}

function closeEditOverlay() {
    document.getElementById('edit-overlay').style.display = 'none';
}
//Delete function
function openDeleteConfirmation(employee_id, name) {
    const confirmationDialog = document.getElementById('delete-confirmation-dialog');
    const deleteIdInput = document.getElementById('delete-id');
    const employeeNameSpan = document.getElementById('employee-name-to-delete');
    
    deleteIdInput.value = employee_id;
    employeeNameSpan.textContent = name;

    confirmationDialog.style.display = 'block';
}

function closeDeleteConfirmation() {
    document.getElementById('delete-confirmation-dialog').style.display = 'none';
}

function searchTable() {
    const input = document.getElementById("search");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("employeeTable");
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        const cells = rows[i].getElementsByTagName("td");
        let match = false;

        // Check all columns for a match
        for (let j = 0; j < cells.length; j++) { // Check all columns
            if (cells[j].textContent.toLowerCase().includes(filter)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? "" : "none"; // Show or hide the row
    }
}


function sortTable() {
    const table = document.getElementById("employeeTable");
    const rows = Array.from(table.rows).slice(1); // Get all rows except the header
    const sortCriteria = document.getElementById("sort").value;

    rows.sort((a, b) => {
        let aValue, bValue;

        switch (sortCriteria) {
            case 'id':
                aValue = parseInt(a.cells[0].textContent); // Employee ID
                bValue = parseInt(b.cells[0].textContent);
                return aValue - bValue; // Sort numerically
            case 'name_asc':
                aValue = a.cells[1].textContent.toLowerCase(); // Employee Name
                bValue = b.cells[1].textContent.toLowerCase();
                return aValue.localeCompare(bValue); // Sort alphabetically A-Z
            case 'name_desc':
                aValue = a.cells[1].textContent.toLowerCase(); // Employee Name
                bValue = b.cells[1].textContent.toLowerCase();
                return bValue.localeCompare(aValue); // Sort alphabetically Z-A
            default:
                return 0; // No sorting
        }
    });

    // Append the sorted rows back to the table body
    const tbody = table.querySelector('tbody');
    tbody.innerHTML = ''; // Clear existing rows
    rows.forEach(row => tbody.appendChild(row)); // Append sorted rows
}