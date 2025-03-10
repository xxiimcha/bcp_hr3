// employee-logout.js

function showDialog() {
    document.getElementById('dialog-overlay').style.display = 'flex';
}

document.getElementById('confirm-button').addEventListener('click', function() {
    // Redirect to logout script or perform logout action
    window.location.href = 'employee-login.php'; // Adjust the path as necessary
});

document.getElementById('dialog-cancel-button').addEventListener('click', function() {
    document.getElementById('dialog-overlay').style.display = 'none';
});


function getWeekOfMonth(date) {
    // Calculate the week number of the month
    const startDate = new Date(date.getFullYear(), date.getMonth(), 1);
    const weekNumber = Math.ceil(((date.getDate() + startDate.getDay()) / 7));
    return weekNumber;
}

function updateDateInfo() {
    const date = new Date();
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const dateString = date.toLocaleDateString(undefined, dateOptions);
    const timeString = date.toLocaleTimeString(undefined, timeOptions);
    const weekOfMonth = getWeekOfMonth(date);
    document.getElementById('date-info').textContent = `${dateString} - ${timeString} (Week ${weekOfMonth})`;
}

// Call the function to update the date info initially
updateDateInfo();

// Update the date info every second
setInterval(updateDateInfo, 1000);

