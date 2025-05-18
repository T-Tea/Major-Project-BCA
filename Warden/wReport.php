<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your DB username
$password = ""; // Replace with your DB password
$dbname = "hostel"; // Replace with your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get rooms with active reports for admin - FIXED to get ALL unread reports
function getRoomsWithReports($conn) {
    $rooms = [];
    // Join with hostellers table to get room numbers - Now getting ALL unread reports for admin
    $sql = "SELECT DISTINCT h.room 
            FROM reports r 
            JOIN hostellers h ON r.user_id = h.id 
            WHERE r.status = 'unread' AND r.recipient = 'warden' 
            ORDER BY h.room ASC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $rooms[] = $row['room'];
        }
    }
    
    return $rooms;
}

// Function to count total unread reports for warden - FIXED to count ALL unread reports
function getUnreadReportsCount($conn) {
    $sql = "SELECT COUNT(*) as count FROM reports WHERE status = 'unread' AND recipient = 'warden'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    return 0;
}

// Function to get all available rooms with reports for warden
function getAllRooms($conn) {
    $rooms = [];
    $sql = "SELECT DISTINCT h.room 
            FROM reports r 
            JOIN hostellers h ON r.user_id = h.id 
            WHERE r.recipient = 'warden' 
            ORDER BY h.room ASC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $rooms[] = $row['room'];
        }
    }
    
    return $rooms;
}

// Function to get report details for a specific room
function getReportByRoom($conn, $room) {
    if ($room == 'all') {
        $sql = "SELECT r.*, h.room, h.name as student_name 
                FROM reports r 
                JOIN hostellers h ON r.user_id = h.id 
                WHERE r.recipient = 'warden' 
                ORDER BY r.created_at DESC LIMIT 1";
    } else {
        $sql = "SELECT r.*, h.room, h.name as student_name 
                FROM reports r 
                JOIN hostellers h ON r.user_id = h.id 
                WHERE h.room = '$room' AND r.recipient = 'warden' 
                ORDER BY r.created_at DESC LIMIT 1";
    }
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Mark a report as read
if (isset($_POST['mark_read']) && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $sql = "UPDATE reports SET status = 'read' WHERE report_id = $report_id";
    $conn->query($sql);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete a report
if (isset($_POST['delete_report']) && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $sql = "DELETE FROM reports WHERE report_id = $report_id";
    $conn->query($sql);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Mark all reports as read
if (isset($_POST['mark_all'])) {
    $sql = "UPDATE reports SET status = 'read' WHERE status = 'unread' AND recipient = 'warden'";
    $conn->query($sql);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get selected room from filter (default to 'all')
$selectedRoom = isset($_GET['room']) ? $_GET['room'] : 'all';

// Get report for selected room
$currentReport = getReportByRoom($conn, $selectedRoom);

// Get rooms with active reports for notifications
$roomsWithReports = getRoomsWithReports($conn);

// Count total unread reports
$unreadCount = getUnreadReportsCount($conn);

// Get all rooms for filter dropdown
$allRooms = getAllRooms($conn);
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
          <div class="reports-section">

            <!-- Filter System -->
            <div class="report-filter">
              <label for="room-select">📁 Filter by Room:</label>
              <select id="room-select" onchange="window.location.href='?room='+this.value">
                <option value="all" <?php echo $selectedRoom == 'all' ? 'selected' : ''; ?>>All Rooms</option>
                <?php foreach($allRooms as $room): ?>
                <option value="<?php echo $room; ?>" <?php echo $selectedRoom == $room ? 'selected' : ''; ?>><?php echo $room; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          
            <div class="report-notification-bar" style="display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
              <div class="notif-message">
                🔔 <span id="new-complaint-count"><?php echo $unreadCount; ?></span> new complaints received.
                <?php if(!empty($roomsWithReports)): ?>
                <div class="rooms-with-reports" style="margin-top: 5px; font-size: 0.9em; color: #555;">
                  Active reports from rooms: <?php echo implode(', ', $roomsWithReports); ?>
                </div>
                <?php endif; ?>
              </div>
              <form method="post" style="display: inline;">
                <button type="submit" name="mark_all" id="clear-notif" style="padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; font-family: 'Poppins', sans-serif; background-color: #2196F3; color: white; transition: all 0.3s ease;">Mark All as Seen</button>
              </form>
            </div>
          
            <div class="report-screen" id="report-screen">
              
              <?php if($currentReport): ?>
              <!-- If complaint is present -->
              <div class="report-card">
                <div class="report-top">
                  <div class="report-title">
                    <h2>🛠️ Complaint from Room <?php echo $currentReport['room']; ?></h2>
                    <p class="report-timestamp">Submitted on: <?php echo date('F j, Y - g:i A', strtotime($currentReport['created_at'])); ?></p>
                  </div>
                  <div class="report-actions">
                    <form method="post" style="display: inline;">
                      <input type="hidden" name="report_id" value="<?php echo $currentReport['report_id']; ?>">
                      <button type="submit" name="mark_read" style="padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; font-family: 'Poppins', sans-serif; background-color: #4CAF50; color: white; transition: all 0.3s ease;">Mark as Read</button>
                      <button type="submit" name="delete_report" style="padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; font-family: 'Poppins', sans-serif; background-color: #f44336; color: white; margin-left: 10px; transition: all 0.3s ease;" onclick="return confirm('Are you sure you want to delete this report?')">Delete</button>
                    </form>
                  </div>
                </div>
          
                <div class="report-content">
                  <div class="report-section1">
                    <h3>📌 Type of Complaint:</h3>
                    <p><?php echo $currentReport['complaint_type']; ?></p>
                  </div>
          
                  <div class="report-section1">
                    <h3>📝 Subject:</h3>
                    <p><?php echo $currentReport['subject']; ?></p>
                  </div>
          
                  <div class="report-section full">
                    <h3>🗒️ Description:</h3>
                    <p><?php echo nl2br($currentReport['description']); ?></p>
                  </div>
          
                  <?php if(!empty($currentReport['attachment_path'])): ?>
                  <div class="report-section">
                    <h3>📎 Attachment:</h3>
                    <a href="<?php echo $currentReport['attachment_path']; ?>" target="_blank">View Uploaded Image</a>
                  </div>
                  <?php endif; ?>
                  
                  <div class="report-section">
                    <h3>👤 Submitted by:</h3>
                    <p><?php echo $currentReport['student_name']; ?></p>
                  </div>
                </div>
              </div>
              <?php else: ?>
              <!-- No complaints found message -->
              <div class="no-reports">
                <p>No complaints found for the selected room. You're all good here! ✅</p>
              </div>
              <?php endif; ?>
          
            </div>
          </div>
        
        </div>
      </div>

    </div>
    
    <script>
      const roomSelect = document.getElementById("room-select");
      const reportCard = document.querySelector(".report-card");
      const noReports = document.querySelector(".no-reports");
      const notifCount = document.getElementById("new-complaint-count");
      const clearNotifBtn = document.getElementById("clear-notif");

      // Example complaints by room (simulate database)
      const complaintRooms = {
        "101": false,
        "102": true,
        "103": false
      };

      // Filter dropdown behavior
      roomSelect.addEventListener("change", () => {
        const selectedRoom = roomSelect.value;
        const hasComplaint = complaintRooms[selectedRoom] || selectedRoom === "all";

        if (hasComplaint) {
          reportCard.classList.remove("hidden");
          noReports.classList.add("hidden");
        } else {
          reportCard.classList.add("hidden");
          noReports.classList.remove("hidden");
        }
      });

      // Clear notifications
      clearNotifBtn.addEventListener("click", () => {
        notifCount.innerText = "0";
        document.querySelector(".report-notification-bar").style.opacity = "0.4";
      });

    </script>

    <script src="wScripts/wScript.js"></script>
  </body>
</html>