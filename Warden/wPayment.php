<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hostel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle filter room request via AJAX
if (isset($_GET['action']) && $_GET['action'] == 'get_hostellers') {
  $room = isset($_GET['room']) ? $_GET['room'] : '';
  
  if (!empty($room)) {
      // Changed room_number to room as per your database structure
      $sql = "SELECT * FROM hostellers WHERE room = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $room);
      $stmt->execute();
      $result = $stmt->get_result();
      
      $hostellers = [];
      
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              // Debug log to see what values are coming from the database
              error_log("Room " . $row['room'] . " - User: " . $row['name'] . 
                        " - Mess Fee: " . var_export($row['mess_fee'], true) . 
                        " - Room Rent: " . var_export($row['room_rent'], true));
              
              // Convert to integer to handle various database return types
              $mess_fee = (isset($row['mess_fee']) && $row['mess_fee'] !== null) ? (int)$row['mess_fee'] : 0;
              $room_rent = (isset($row['room_rent']) && $row['room_rent'] !== null) ? (int)$row['room_rent'] : 0;
              
              $hostellers[] = [
                  'id' => $row['id'],
                  'room' => $row['room'],
                  'name' => $row['name'],
                  'mess_fee_status' => $mess_fee == 1 ? 'Paid' : 'Unpaid',
                  'room_rent_status' => $room_rent == 1 ? 'Paid' : 'Unpaid'
              ];
          }
      }
      
      // Return JSON response
      header('Content-Type: application/json');
      echo json_encode($hostellers);
      exit;
  }
}


// Handle get payment requests via AJAX

else if (isset($_GET['action']) && $_GET['action'] == 'get_payment_requests') {
  try {
      // Updated query to use request_id as primary key and user_id as foreign key
      $sql = "SELECT pr.request_id, h.room as room, h.name as occupant, pr.fee_type, pr.requested_change, pr.status 
              FROM payment_request pr
              JOIN hostellers h ON pr.user_id = h.id
              WHERE pr.status = 'pending'
              ORDER BY pr.request_id DESC";

      $result = $conn->query($sql);
      
      if ($result === false) {
          // If query failed, return the error
          header('Content-Type: application/json');
          echo json_encode(['error' => true, 'message' => 'SQL Error: ' . $conn->error]);
          exit;
      }
      
      $requests = [];

      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $requests[] = $row;
          }
      }

      // Return JSON response
      header('Content-Type: application/json');
      echo json_encode($requests);
  } catch (Exception $e) {
      header('Content-Type: application/json');
      echo json_encode(['error' => true, 'message' => 'PHP Error: ' . $e->getMessage()]);
  }
  exit;
}


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
          <div class="payment-tabs">
            <button class="tab-button active" onclick="showTab('occupants')">Occupant Payment Status</button>
            <button class="tab-button" onclick="showTab('requests')">Display Requests</button>
          </div>
          
          <div class="tab-content" id="occupants">
            <h2>Occupant Fee Payment Status</h2>
            <label for="roomFilter">Filter by Room:</label>
            <select id="roomFilter" onchange="filterByRoom()">
              <option value="">-- Select Room --</option>
              <?php
              // Fetch unique room numbers - changed room_number to room
              $sql = "SELECT DISTINCT room FROM hostellers ORDER BY room";
              $result = $conn->query($sql);
              
              if ($result->num_rows > 0) {
                  while($row = $result->fetch_assoc()) {
                      echo "<option value=\"" . $row["room"] . "\">Room " . $row["room"] . "</option>";
                  }
              }
              ?>
            </select>           
            
            <div id="defaultMessage" class="default-message">
              <p>Please select a room to view payment status.</p>
            </div>
            
            <table class="occupants-table" id="occupantsTable" style="display: none;">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Occupant Name</th>
                  <th>Mess Fee</th>
                  <th>Room Rent</th>
                </tr>
              </thead>
              <tbody>
                <!-- Dynamically inserted rows -->
              </tbody>
            </table>
          
            
          </div>
          
          <div class="tab-content hidden" id="requests">
            <h2>Fee Status Change Requests</h2>
            <table class="payment-table">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Occupant</th>
                  <th>Fee Type</th>
                  <th>Requested Change</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- Dynamically inserted rows -->
              </tbody>
            </table>
          </div>
              

        </div>
      </div>

    </div>
    
    <script src="wScripts/wScript.js"></script>
    <script src="wScripts/wPayScript.js"></script>
  </body>
</html>