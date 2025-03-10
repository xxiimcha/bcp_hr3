
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.querySelector('.popup');
            const message = "<?php echo htmlspecialchars($message); ?>";

            if (message) {
                popup.querySelector('p').textContent = message;
                popup.classList.add('show');
                setTimeout(function() {
                    window.location.href = 'log-in.php';
                }, 2000); // Redirect after 2 seconds
            }
        });
 

        