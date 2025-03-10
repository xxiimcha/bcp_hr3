    // Function to toggle dark mode
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');

        // Save the current mode to localStorage
        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    }

    // Check localStorage for the saved theme on page load
    window.onload = function() {
        const savedTheme = localStorage.getItem('theme');

        // If the saved theme is 'dark', apply dark mode
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    };

    // Add event listener to the button
    document.getElementById('darkModeToggle').addEventListener('click', toggleDarkMode);