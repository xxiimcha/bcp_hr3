
// Function to handle the logout overlay
function showLogoutOverlay() {
    document.getElementById('logoutOverlay').style.display = 'flex';
}

function closeOverlay() {
    document.getElementById('logoutOverlay').style.display = 'none';
}

function confirmLogout() {
    window.location.href = 'portal-logout.php'; // Make sure to set your logout URL
}

// Function to toggle sidebar visibility
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

// Function to toggle the sidebar visibility
function toggleSidebar() {
    // Get the sidebar element
    const sidebar = document.querySelector('.sidebar');
    
    // Toggle the 'sidebar-hidden' class to show/hide the sidebar
    sidebar.classList.toggle('sidebar-hidden');
    
    // Optionally, you can also toggle a class for the content area if needed
    // const content = document.querySelector('.content');
    // content.classList.toggle('sidebar-hidden');
}
// Function to toggle the visibility of the overlay (modal)
function toggleOverlay() {
    const overlay = document.getElementById('requestLeaveOverlay');
    
    // Toggle the display between 'none' and 'block'
    if (overlay.style.display === 'none' || overlay.style.display === '') {
        overlay.style.display = 'flex'; // Show the overlay
    } else {
        overlay.style.display = 'none'; // Hide the overlay
    }
}
    // Select the dark mode toggle button and icon
    const darkModeButton = document.querySelector('.dark-mode-toggle');
    const darkModeIcon = document.getElementById('dark-mode-icon');

    // Function to toggle dark mode
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode'); // Toggle the dark mode class

        // Change the icon based on the mode
        if (document.body.classList.contains('dark-mode')) {
            darkModeIcon.classList.remove('fa-sun'); // Remove the sun icon
            darkModeIcon.classList.add('fa-moon'); // Add the moon icon for dark mode
            localStorage.setItem('dark-mode', 'enabled'); // Save dark mode state
        } else {
            darkModeIcon.classList.remove('fa-moon'); // Remove the moon icon
            darkModeIcon.classList.add('fa-sun'); // Add the sun icon for light mode
            localStorage.removeItem('dark-mode'); // Remove the dark mode state
        }
    }

    // On page load, check if dark mode was previously enabled and apply it
    window.addEventListener('load', () => {
        if (localStorage.getItem('dark-mode') === 'enabled') {
            document.body.classList.add('dark-mode');
            darkModeIcon.classList.add('fa-moon'); // Set the moon icon for dark mode
        } else {
            darkModeIcon.classList.add('fa-sun'); // Set the sun icon for light mode
        }
    });