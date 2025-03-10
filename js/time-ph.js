function getWeekOfMonth(date) {
    // Calculate the week number of the month based on Philippine timezone
    const currentDay = date.getDate();
    
    // Determine the week number based on the current day of the month
    let weekNumber;
    if (currentDay <= 7) {
        weekNumber = 1; // Days 1-7
    } else if (currentDay <= 14) {
        weekNumber = 2; // Days 8-14
    } else if (currentDay <= 21) {
        weekNumber = 3; // Days 15-21
    } else {
        weekNumber = 4; // Days 22-30
    }
    
    // Ensure week number does not exceed total weeks in the month
    const totalDaysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    const totalWeeksInMonth = Math.ceil(totalDaysInMonth / 7);
    
    // Return the week number, ensuring it does not exceed total weeks
    return Math.min(weekNumber, totalWeeksInMonth);
}

function updateDateInfo() {
    // Get current date in Philippine timezone
    const options = { timeZone: 'Asia/Manila' };
    const date = new Date().toLocaleString('en-PH', options);
    const dateObject = new Date(date); // Convert back to Date object for further calculations

    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'Asia/Manila' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Manila' };

    const dateString = new Intl.DateTimeFormat('en-PH', dateOptions).format(dateObject);
    const timeString = new Intl.DateTimeFormat('en-PH', timeOptions).format(dateObject);
    const weekOfMonth = getWeekOfMonth(dateObject);
    
    document.getElementById('date-info').textContent = `${dateString} - ${timeString} (Week ${weekOfMonth})`;
}

// Call the function to update the date info initially
updateDateInfo();

// Update the date info every second
setInterval(updateDateInfo, 1000);


function showDialog() {
    document.getElementById('dialog-overlay').style.display = 'flex';
}

document.getElementById('cancel-button').onclick = function() {
    document.getElementById('dialog-overlay').style.display = 'none';
}

document.getElementById('confirm-button').onclick = function() {
    window.location.href = '../index.php';
}