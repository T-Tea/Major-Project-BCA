document.addEventListener('DOMContentLoaded', function() {
    // Get form element
    const checkoutForm = document.getElementById('checkout-form');
    const messageContainer = document.getElementById('message-container');
    
    // Add submit event listener
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Create FormData object
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Create message element
                const messageDiv = document.createElement('div');
                messageDiv.className = data.status === 'success' ? 'success-message' : 'error-message';
                messageDiv.textContent = data.message;
                
                // Clear existing messages
                messageContainer.innerHTML = '';
                
                // Add message to container
                messageContainer.appendChild(messageDiv);
                
                // If successful, reset the form
                if (data.status === 'success') {
                    checkoutForm.reset();
                    
                    // Auto-hide the message after 5 seconds
                    setTimeout(() => {
                        messageDiv.style.opacity = '0';
                        setTimeout(() => {
                            messageContainer.innerHTML = '';
                        }, 500);
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Create error message
                const messageDiv = document.createElement('div');
                messageDiv.className = 'error-message';
                messageDiv.textContent = 'An error occurred. Please try again later.';
                
                // Clear existing messages
                messageContainer.innerHTML = '';
                
                // Add message to container
                messageContainer.appendChild(messageDiv);
            });
        });
    }
});