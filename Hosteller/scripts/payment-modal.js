// payment-modal.js

document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const modal = document.getElementById('payment-modal');
    const payButton = document.getElementById('pay-fees-btn');
    const closeBtn = document.querySelector('.close');
    const feeTypeSelect = document.getElementById('fee-type');
    const amountInput = document.getElementById('amount');
    const paymentForm = document.getElementById('payment-form');
  
    // Open modal when pay button is clicked
    payButton.addEventListener('click', function() {
      modal.style.display = 'block';
    });
  
    // Close modal when X is clicked
    closeBtn.addEventListener('click', function() {
      modal.style.display = 'none';
    });
  
    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    });
  
    // Update amount when fee type changes
    feeTypeSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      if (selectedOption.value) {
        const amount = selectedOption.getAttribute('data-amount');
        amountInput.value = '₹ ' + amount;
      } else {
        amountInput.value = '';
      }
    });
  
    // Form validation before submission
    paymentForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Basic validation
      if (!feeTypeSelect.value) {
        alert('Please select a fee type');
        return;
      }
      
      // For dummy payment, simulate loading state
      const submitButton = this.querySelector('.payment-btn');
      submitButton.textContent = 'Processing...';
      submitButton.disabled = true;
      
      // Simulate payment processing delay
      setTimeout(() => {
        // You would normally submit this to your server
        // Here we'll just simulate a successful payment
        simulatePaymentSuccess();
      }, 2000);
    });
    
    function simulatePaymentSuccess() {
      // Get form data
      const feeType = feeTypeSelect.value;
      const feeAmount = feeType === 'room_rent' ? '1500' : '15000';
      const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
      
      // Generate a random transaction ID
      const transactionId = 'TXN' + Math.floor(Math.random() * 10000000);
      
      // Send data to server using fetch
      fetch('xtraBackendLogic/process_dummy_payment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          'fee_type': feeType,
          'amount': feeAmount,
          'payment_method': paymentMethod,
          'transaction_id': transactionId,
          'user_id': document.querySelector('input[name="user_id"]').value,
          'room': document.querySelector('input[name="room"]').value,
          'building': document.querySelector('input[name="building"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Display success message
          displaySuccessMessage(feeType, feeAmount, transactionId);
          
          // Reset form and close modal
          paymentForm.reset();
          modal.style.display = 'none';
          
          // Refresh page after a delay to show updated payment status
          setTimeout(() => {
            window.location.reload();
          }, 3000);
        } else {
          alert('Payment processing failed: ' + data.message);
          resetSubmitButton();
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during payment processing. Please try again.');
        resetSubmitButton();
      });
    }
    
    function resetSubmitButton() {
      const submitButton = paymentForm.querySelector('.payment-btn');
      submitButton.textContent = 'Process Payment';
      submitButton.disabled = false;
    }
    
    function displaySuccessMessage(feeType, amount, transactionId) {
      // Create success alert
      const successAlert = document.createElement('div');
      successAlert.className = 'alert alert-success';
      successAlert.innerHTML = `
        <strong>Payment Successful!</strong><br>
        ${feeType === 'room_rent' ? 'Room Rent' : 'Mess Fee'} payment of ₹${amount} completed.<br>
        Transaction ID: ${transactionId}
      `;
      
      // Add to page
      const paymentContainer = document.querySelector('.payment-container');
      paymentContainer.insertBefore(successAlert, paymentContainer.firstChild);
      
      // Remove the alert after 5 seconds
      setTimeout(() => {
        successAlert.remove();
      }, 5000);
    }
  });