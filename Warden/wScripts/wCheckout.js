document.addEventListener('DOMContentLoaded', function() {
  // Initial load of checkout requests
  let currentPage = 1;
  loadCheckoutRequests();
  
  // Event listeners for filters
  document.getElementById('status-filter').addEventListener('change', function() {
      currentPage = 1;
      loadCheckoutRequests();
  });
  
  // Event listener for search
  const searchInput = document.getElementById('search-input');
  let searchTimeout;
  searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function() {
          currentPage = 1;
          loadCheckoutRequests();
      }, 500); // Debounce the search input
  });
  
  // Event listener for load more button
  document.getElementById('load-more-btn').addEventListener('click', function() {
      currentPage++;
      loadCheckoutRequests(true);
  });
  
  // Event delegation for approve/reject buttons
  document.querySelector('.requests-list').addEventListener('click', function(event) {
      // Handle approval button click
      if (event.target.classList.contains('approve-btn') || 
          (event.target.parentElement && event.target.parentElement.classList.contains('approve-btn'))) {
          const requestCard = getParentRequestCard(event.target);
          const requestId = requestCard.dataset.requestId;
          processRequest(requestId, 'approve');
      }
      
      // Handle reject button click
      if (event.target.classList.contains('reject-btn') || 
          (event.target.parentElement && event.target.parentElement.classList.contains('reject-btn'))) {
          const requestCard = getParentRequestCard(event.target);
          const requestId = requestCard.dataset.requestId;
          
          // Show rejection reason prompt
          const reason = prompt("Please provide a reason for rejection:", "");
          if (reason !== null) {
              processRequest(requestId, 'reject', reason);
          }
      }
      
      // Handle view details button (toggles expanded view)
      if (event.target.classList.contains('view-details-btn') ||
          (event.target.parentElement && event.target.parentElement.classList.contains('view-details-btn'))) {
          const requestCard = getParentRequestCard(event.target);
          requestCard.classList.toggle('expanded');
          console.log('Expanded state:', requestCard.classList.contains('expanded'));
      }
      
      // Handle attachment link click
      if (event.target.classList.contains('attachment-link') ||
          (event.target.parentElement && event.target.parentElement.classList.contains('attachment-link'))) {
          event.preventDefault();
          const requestCard = getParentRequestCard(event.target);
          const requestId = requestCard.dataset.requestId;
          window.location.href = '?download=true&id=' + requestId;
      }
  });

  // Function to find the parent request card
  function getParentRequestCard(element) {
      let current = element;
      while (current && !current.classList.contains('request-card')) {
          current = current.parentElement;
      }
      return current;
  }
  
  // Function to load checkout requests
  function loadCheckoutRequests(append = false) {
      const statusFilter = document.getElementById('status-filter').value;
      const searchTerm = document.getElementById('search-input').value;
      const requestsList = document.querySelector('.requests-list');
      const loadMoreBtn = document.getElementById('load-more-btn');
      
      // Show loading state
      if (!append) {
          requestsList.innerHTML = '<div class="loading">Loading requests...</div>';
      }
      
      // Prepare the API URL with filters
      const apiUrl = `?ajax=getCheckouts&status=${statusFilter}&search=${encodeURIComponent(searchTerm)}&page=${currentPage}`;
      
      // Fetch data from the server
      fetch(apiUrl)
          .then(response => {
            return response.text();
              })
              .then(text => {
                  console.log('Raw server response:', text);
                  try {
                      const data = JSON.parse(text);
                      console.log('Parsed data:', data);
                      return data;
                  } catch(e) {
                      console.error('JSON parse error:', e);
                      throw new Error('Invalid JSON response from server');
                  }
              })
          .then(data => {
              // Clear existing content if not appending
              if (!append) {
                  requestsList.innerHTML = '';
              }
              
              if (data.data.length === 0 && !append) {
                  requestsList.innerHTML = '<div class="no-requests">No checkout requests found.</div>';
                  loadMoreBtn.style.display = 'none';
                  return;
              }

              if (!data || !data.data) {
                console.error('Unexpected response format:', data);
                requestsList.innerHTML = '<div class="error">Server returned unexpected data format.</div>';
                return;
            }
              
              // Render each request
              data.data.forEach(request => {
                  const requestCard = createRequestCard(request);
                  requestsList.appendChild(requestCard);
              });
              
              // Update load more button visibility
              loadMoreBtn.style.display = data.pagination.has_more ? 'block' : 'none';
          })
          .catch(error => {
            console.error('Error loading checkout requests:', error);
            requestsList.innerHTML = '<div class="error">Failed to load requests. Please try again.</div>';
        });
  }
  
  // Function to create a request card element
  function createRequestCard(request) {
      const requestCard = document.createElement('div');
      requestCard.className = `request-card ${request.status}`;
      requestCard.dataset.requestId = request.id;
      
      // Create request ID for display (REQ-YYYY-XXXX format)
      const submitDate = new Date(request.submit_datetime);
      const requestDisplayId = `REQ-${submitDate.getFullYear()}-${String(request.id).padStart(4, '0')}`;
      
      // Generate attachment HTML
      let attachmentHtml = '';
      if (request.file_path) {
          attachmentHtml = `
              <div class="detail-row attachment">
                  <span class="label">Attachment:</span>
                  <span class="value">
                      <a href="#" class="attachment-link">
                          <i class="fas fa-paperclip"></i> ${request.file_name}
                      </a>
                  </span>
              </div>
          `;
      } else {
          attachmentHtml = `
              <div class="detail-row attachment">
                  <span class="label">Attachment:</span>
                  <span class="value">No attachment provided</span>
              </div>
          `;
      }
      
      // Generate rejection reason HTML if applicable
      let rejectionReasonHtml = '';
      if (request.status === 'rejected' && request.warden_remarks) {
          rejectionReasonHtml = `
              <div class="detail-row">
                  <span class="label">Rejection Reason:</span>
                  <span class="value rejection-reason">${request.warden_remarks}</span>
              </div>
          `;
      }
      
      // Generate action buttons based on status
      let actionButtonsHtml = '';
      if (request.status === 'pending') {
          actionButtonsHtml = `
              <button class="btn approve-btn"><i class="fas fa-check"></i> Approve</button>
              <button class="btn reject-btn"><i class="fas fa-times"></i> Reject</button>
          `;
      } else {
          actionButtonsHtml = `
              <button class="btn view-details-btn"><i class="fas fa-eye"></i> View Details</button>
          `;
      }
      
      requestCard.innerHTML = `
          <div class="request-header">
              <div class="student-info">
                  <h3>${request.hosteller_name}</h3>
                  <div class="details">
                      <span><i class="fas fa-door-open"></i> Room ${request.room_number}</span>
                      <span><i class="fas fa-graduation-cap"></i> ${request.semester}th Semester</span>
                      <span class="request-status ${request.status}">${capitalizeFirstLetter(request.status)}</span>
                  </div>
              </div>
              <div class="request-actions">
                  ${actionButtonsHtml}
              </div>
          </div>
          <div class="request-details">
              <div class="detail-row">
                  <span class="label">Request ID:</span>
                  <span class="value">#${requestDisplayId}</span>
              </div>
              <div class="detail-row">
                  <span class="label">Subject:</span>
                  <span class="value">${request.subject}</span>
              </div>
              <div class="detail-row">
                  <span class="label">Checkout Time:</span>
                  <span class="value">${request.date_range}</span>
              </div>
              <div class="detail-row">
                  <span class="label">Reason:</span>
                  <span class="value">${request.description}</span>
              </div>
              ${attachmentHtml}
              ${rejectionReasonHtml}
          </div>
      `;
      
      return requestCard;
  }
  
  // Function to process approve/reject actions
  function processRequest(requestId, action, remarks = '') {
      const formData = new FormData();
      formData.append('request_id', requestId);
      formData.append('action', action);
      formData.append('remarks', remarks);
      
      fetch(window.location.href, {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              // Reload the requests to reflect changes
              loadCheckoutRequests();
              
              // Show success message
              alert(`Request ${action}d successfully.`);
          } else {
              alert('Error: ' + data.message);
          }
      })
      .catch(error => {
          console.error('Error processing request:', error);
          alert('Failed to process the request. Please try again.');
      });
  }
  
  // Helper function to capitalize first letter
  function capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
  }
});