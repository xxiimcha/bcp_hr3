  // JavaScript to toggle admin password visibility
  document.getElementById('showPassword').addEventListener('change', function() {
    const passwordField = document.getElementById('password');
    passwordField.type = this.checked ? 'text' : 'password';
});




// Get all navigation buttons
const navButtons = document.querySelectorAll('.nav-btn');

// Add event listeners to all buttons
navButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove the 'active' class from all buttons
        navButtons.forEach(btn => btn.classList.remove('active'));

        // Add the 'active' class to the clicked button
        button.classList.add('active');

        // Toggle visibility of the sections based on button clicked
        if (button.id === 'loginBtn') {
            document.getElementById('loginSection').style.display = 'block';
            document.getElementById('forgotPasswordSection').style.display = 'none';
        } else if (button.id === 'forgotPasswordBtn') {
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('forgotPasswordSection').style.display = 'block';
        }
    });
});

