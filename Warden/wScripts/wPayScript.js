
document.addEventListener('DOMContentLoaded', function() {
  // Initialize default view
  showTab('occupants');
  
  // Load payment requests
  loadPaymentRequests();
});

// Show/hide tabs
function showTab(tabName) {
  // Hide all tabs
  document.querySelectorAll('.tab-content').forEach(tab => {
      tab.classList.add('hidden');
  });
  
  // Deactivate all tab buttons
  document.querySelectorAll('.tab-button').forEach(button => {
      button.classList.remove('active');
  });
  
  // Show selected tab and activate button
  document.getElementById(tabName).classList.remove('hidden');
  
  // Find and activate the button for this tab
  document.querySelectorAll('.tab-button').forEach(button => {
      if (button.getAttribute('onclick').includes(tabName)) {
          button.classList.add('active');
      }
  });
  
  // Show default message if occupants tab
  if (tabName === 'occupants') {
      document.getElementById('defaultMessage').style.display = 'block';
      document.getElementById('occupantsTable').style.display = 'none';
      //document.querySelector('.save-button').style.display = 'none';
  }
  
  // Load request data if requests tab
  if (tabName === 'requests') {
      loadPaymentRequests();
  }
}

// Filter hostellers by room
function filterByRoom() {
  const roomNumber = document.getElementById('roomFilter').value;
  
  if (roomNumber === '') {
      // Show default message when no room is selected
      document.getElementById('defaultMessage').style.display = 'block';
      document.getElementById('occupantsTable').style.display = 'none';
      //document.querySelector('.save-button').style.display = 'none';
      return;
  }

// Fetch hostellers data for the selected room
  fetch(`?action=get_hostellers&room=${roomNumber}`)
  .then(response => response.json())
  .then(data => {
      console.log("Received hosteller data:", data);
      const tableBody = document.querySelector('#occupantsTable tbody');
      tableBody.innerHTML = ''; // Clear existing rows
      
      if (data.length > 0) {
          // Hide default message and show table
          document.getElementById('defaultMessage').style.display = 'none';
          document.getElementById('occupantsTable').style.display = 'table';
          //document.querySelector('.save-button').style.display = 'block';
          
          // Add rows for each hosteller
          data.forEach(hosteller => {
              const row = document.createElement('tr');
              row.setAttribute('data-id', hosteller.id);
              row.setAttribute('data-room', hosteller.room);
              
              // Ensure the button class matches the status
              const messFeeClass = hosteller.mess_fee_status.toLowerCase();
              const roomRentClass = hosteller.room_rent_status.toLowerCase();
              
              row.innerHTML = `
                  <td>${hosteller.room}</td>
                  <td>${hosteller.name}</td>
                  <td><span class="${messFeeClass}">${hosteller.mess_fee_status === 'Paid' ? 'Paid' : 'Unpaid'}</span></td>
                  <td><span class="${roomRentClass}">${hosteller.room_rent_status === 'Paid' ? 'Paid' : 'Unpaid'}</span></td>
              `;

              tableBody.appendChild(row);
          });
      } else {
          // No hostellers found for this room
          document.getElementById('defaultMessage').style.display = 'block';
          document.getElementById('defaultMessage').innerHTML = `<p>No hostellers found for Room ${roomNumber}.</p>`;
          document.getElementById('occupantsTable').style.display = 'none';
          //document.querySelector('.save-button').style.display = 'none';
      }
  })
  .catch(error => {
      console.error('Error fetching hostellers:', error);
      document.getElementById('defaultMessage').style.display = 'block';
      document.getElementById('defaultMessage').innerHTML = '<p>Error loading hostellers data. Please try again.</p>';
      document.getElementById('occupantsTable').style.display = 'none';
      //document.querySelector('.save-button').style.display = 'none';
  });
}

/// Toggle payment status button
function toggleStatus(button) {
  // First, clear any existing status classes
  button.classList.remove('paid', 'unpaid');
  
  if (button.innerText === 'Paid') {
      button.innerText = 'Unpaid';
      button.classList.add('unpaid');
  } else {
      button.innerText = 'Paid';
      button.classList.add('paid');
  }
  
  // Mark button as changed
  button.classList.add('changed');
}

// Save payment status changes
function saveChanges() {
  const changedButtons = document.querySelectorAll('.toggle-btn.changed');
  
  if (changedButtons.length === 0) {
      alert('No changes to save!');
      return;
  }
  
  let promises = [];
  
  changedButtons.forEach(button => {
      const row = button.closest('tr');
      const id = row.getAttribute('data-id');
      const type = button.getAttribute('data-type');
      const status = button.innerText;
      
      // Create form data
      const formData = new FormData();
      formData.append('action', 'update_payment');
      formData.append('id', id);
      formData.append('type', type);
      formData.append('status', status);
      
      // Send update request
      const promise = fetch('', {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              // Remove changed class if successful
              button.classList.remove('changed');
              return true;
          } else {
              console.error('Error updating status:', data.message);
              return false;
          }
      })
      .catch(error => {
          console.error('Error:', error);
          return false;
      });
      
      promises.push(promise);
  });
  
  // Wait for all updates to complete
  Promise.all(promises)
      .then(results => {
          const allSuccessful = results.every(result => result === true);
          if (allSuccessful) {
              alert('All payment statuses updated successfully!');
          } else {
              alert('Some payment status updates failed. Please try again.');
          }
      });
}

// Load payment requests
function loadPaymentRequests() {
fetch('?action=get_payment_requests')
  .then(response => {
      if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.statusText);
      }
      return response.json();
  })
  .then(data => {
      // Check if data contains an error message
      if (data && data.error) {
          console.error('Server error:', data.message);
          throw new Error(data.message);
      }
      
      const tableBody = document.querySelector('#requests table tbody');
      tableBody.innerHTML = ''; // Clear existing rows
      
      if (data.length > 0) {
          data.forEach(request => {
              const row = document.createElement('tr');
              // Changed from id to request_id
              row.setAttribute('data-id', request.request_id);
              
              row.innerHTML = `
                  <td>${request.room}</td>
                  <td>${request.occupant}</td>
                  <td>${request.fee_type}</td>
                  <td>${request.requested_change}</td>
                  <td>
                      <button class="approve-btn" onclick="updateRequestStatus(${request.request_id}, 'approve')">Approve</button>
                      <button class="reject-btn" onclick="updateRequestStatus(${request.request_id}, 'reject')">Reject</button>
                  </td>
              `;
              
              tableBody.appendChild(row);
          });
      } else {
          // No pending requests
          const row = document.createElement('tr');
          row.innerHTML = `
              <td colspan="5" class="no-requests">No pending payment requests found.</td>
          `;
          tableBody.appendChild(row);
      }
  })
  .catch(error => {
      console.error('Error loading payment requests:', error);
      const tableBody = document.querySelector('#requests table tbody');
      tableBody.innerHTML = `
          <tr>
              <td colspan="5" class="error-message">Error loading payment requests: ${error.message}</td>
          </tr>
      `;
  });
}

// Update payment request status
function updateRequestStatus(id, status_action) {
  // Create form data
  const formData = new FormData();
  formData.append('action', 'update_request_status');
  formData.append('id', id);
  formData.append('status_action', status_action);
  
  // Send update request
  fetch('', {
      method: 'POST',
      body: formData
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          // Remove the row from the table
          const row = document.querySelector(`#requests tr[data-id="${id}"]`);
          if (row) {
              row.remove();
          }
          
          // Check if table is now empty
          const tableBody = document.querySelector('#requests table tbody');
          if (tableBody.children.length === 0) {
              const row = document.createElement('tr');
              row.innerHTML = `
                  <td colspan="5" class="no-requests">No pending payment requests found.</td>
              `;
              tableBody.appendChild(row);
          }
          
          alert(`Request ${status_action}d successfully!`);
      } else {
          alert(`Error: ${data.message}`);
      }
  })
  .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while processing the request.');
  });
}
