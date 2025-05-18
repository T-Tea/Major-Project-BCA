<?php
// Add at the top of your file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "hostel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Handle file downloads
if (isset($_GET['download']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get the file path from the database
    $stmt = $conn->prepare("SELECT file_path FROM checkout WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();
    
    // Check if file exists and is accessible
    if (empty($file_path) || !file_exists($file_path)) {
        die("File not found");
    }
    
    // Get file information
    $file_name = basename($file_path);
    $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
    $file_size = filesize($file_path);
    
    // Set appropriate mime type based on file extension
    $mime_types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'txt' => 'text/plain',
    ];
    
    $mime_type = isset($mime_types[strtolower($file_extension)]) 
        ? $mime_types[strtolower($file_extension)] 
        : 'application/octet-stream';
    
    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . $file_size);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Clean output buffer
    ob_clean();
    flush();
    
    // Read the file and output it to the browser
    readfile($file_path);
    exit;
}

// Process AJAX requests for checkout data
if (isset($_GET['ajax']) && $_GET['ajax'] === 'getCheckouts') {
    error_log('AJAX request received: ' . $_SERVER['REQUEST_URI']);
    // Get filters from request
    ob_start();
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search_term = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $items_per_page = 5;
    $offset = ($page - 1) * $items_per_page;
    
    // Build the SQL query with filters
    $sql = "SELECT c.*, h.name as hosteller_name, h.room, h.semester 
            FROM checkout c
            JOIN hostellers h ON c.hosteller_id = h.id
            WHERE 1=1";
    
    if ($status_filter !== 'all') {
        $sql .= " AND c.status = '$status_filter'";
    }
    
    if (!empty($search_term)) {
      $search_term = $conn->real_escape_string($search_term);
      $sql .= " AND (h.name LIKE '%$search_term%' OR h.room LIKE '%$search_term%')";
  }
    
    $sql .= " ORDER BY c.submit_datetime DESC LIMIT $offset, $items_per_page";
    
    $result = $conn->query($sql);

    if (!$result) {
      $response = [
          'success' => false,
          'error' => 'Database query failed: ' . $conn->error,
          'sql' => $sql
      ];
      header('Content-Type: application/json');
      echo json_encode($response);
      exit;
    }
    
    // Count total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM checkout c
              JOIN hostellers h ON c.hosteller_id = h.id
              WHERE 1=1";

    if ($status_filter !== 'all') {
        $count_sql .= " AND c.status = '$status_filter'";
    }

    if (!empty($search_term)) {
        $search_term = $conn->real_escape_string($search_term);
        // Update this line to use h.room instead of h.room_number
        $count_sql .= " AND (h.name LIKE '%$search_term%' OR h.room LIKE '%$search_term%')";
    }
    
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $total_records = $count_row['total'];
    $total_pages = ceil($total_records / $items_per_page);
    
    // Prepare data for JSON response
    $requests = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Format dates for display
            $from_datetime = new DateTime($row['from_datetime']);
            $to_datetime = new DateTime($row['to_datetime']);
            $formatted_date = $from_datetime->format('F j, Y - g:i A') . ' to ' . $to_datetime->format('g:i A');
            
            $requests[] = [
                'id' => $row['id'],
                'hosteller_id' => $row['hosteller_id'],
                'hosteller_name' => $row['hosteller_name'],
                'room_number' => $row['room'],
                'semester' => $row['semester'],
                'subject' => $row['subject'],
                'date_range' => $formatted_date,
                'description' => $row['description'],
                'file_path' => $row['file_path'],
                'file_name' => $row['file_path'] ? basename($row['file_path']) : null,
                'status' => $row['status'],
                'warden_remarks' => $row['warden_remarks'],
                'submit_datetime' => date('Y-m-d H:i:s', strtotime($row['submit_datetime'])),
                'response_datetime' => $row['response_datetime'] ? date('Y-m-d H:i:s', strtotime($row['response_datetime'])) : null
            ];
        }
    }
    
    // Create response data
    $response = [
        'success' => true,
        'data' => $requests,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'has_more' => ($page < $total_pages)
        ]
    ];
    
    // Return response as JSON
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Process approve/reject actions via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : "";
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $stmt = $conn->prepare("UPDATE checkout SET status = ?, warden_remarks = ?, response_datetime = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $status, $remarks, $request_id);
        
        $response = [];
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Request ' . $status . ' successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Error updating request: ' . $conn->error];
        }
        $stmt->close();
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Main HTML content continues below if none of the above conditions are met
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="wStyles/wStyle.css">
    <link rel="website icon" type="png" href="images/Hosteller/Home/airplane.png">

  </head>
  <body>
    <div class="dashboard-container">

      <nav class="left-nav">
        <div class="title">
            <img src="../images/Hosteller/Home/airplane.png">
            <p class="nielit">NIELIT Hostel</p>
        </div>
        <ul>
          <li class="home"><img src="../images/Hosteller/Home/home-icon-silhouette.png" class="home-icon"> <span>Home</span></li>
          <li class="notification"><img src="../images/Hosteller/Home/notification.png" class="notification-icon"> <span>Notifications</span></li>
          <li class="manage"><img src="../images/Hosteller/Home/multiple-users-silhouette.png" class="status-icon"> <span>View Hostellers</span></li>
          <li class="checkout"><img src="../images/Hosteller/Home/logout.png" class="status-icon"> <span>Checkout Requests</span></li>
          <li class="payments"><img src="../images/Hosteller/Home/cash-payment.png" class="payments-icon"> <span>Payments</span></li>
          <li class="reports"><img src="../images/Hosteller/Home/report.png" class="reports-icon"> <span>Reports</span></li>
          
      </ul>
      </nav>

      <div class="right-content">

        <div class="nav-bar">
          <div>
            <h1>Warden Dashboard</h1>
          </div>
          <div class="nav-icon">
            <div><i class="fas fa-power-off menu-icon" id="logout-btn"></i></div>
            <div id="tooltip-user" class="tooltip-target"><i class="fas fa-user-circle menu-icon"></i></div>
          </div>
        </div>

        <div class="right-container">
          <div class="checkout-requests-container">
            <div class="checkout-header">
              <h2>Checkout Requests</h2>
              <div class="filter-options">
                <select id="status-filter">
                  <option value="all">All Requests</option>
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </select>
                <div class="search-box">
                  <input type="text" id="search-input" placeholder="Search by name or room...">
                  <i class="fas fa-search"></i>
                </div>
              </div>
            </div>
            
            <div class="requests-list">
              <!-- Request cards will be loaded dynamically -->
              <div class="loading">Loading checkout requests...</div>
            </div>
            
            <!-- Loading More Indicator -->
            <div class="load-more">
              <button id="load-more-btn" style="display: none;">Load More Requests</button>
            </div>
          </div>
        </div>
      </div>

    </div>
    
    <script src="wScripts/wCheckout.js"></script>
    <script src="wScripts/wscript.js"></script>
  </body>
</html>