<?php
// Start session
session_start();

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
  header("Location: ../login.php"); // Redirect to your login page
  exit;
}

// Database connection
$servername = "localhost";
$username = "root"; // Change to your DB username
$password = ""; // Change to your DB password
$dbname = "hostel"; // Change to your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get stats
$total_rooms = 40; // Default value

// Get occupied rooms count
$occupied_query = "SELECT COUNT(*) as occupied_count FROM hostellers WHERE room IS NOT NULL";
$occupied_result = $conn->query($occupied_query);
$occupied_row = $occupied_result->fetch_assoc();
$occupied = $occupied_row['occupied_count'];

// Calculate vacant rooms
$vacant = $total_rooms - $occupied;

// Get issues count
$issues_query = "SELECT COUNT(*) as issues_count FROM reports";
$issues_result = $conn->query($issues_query);
$issues_row = $issues_result->fetch_assoc();
$issues = $issues_row['issues_count'];

// Get upcoming checkouts
$checkouts_query = "SELECT c.*, h.id as hosteller_id, h.room, CONCAT(h.name) as full_name 
                   FROM checkout c 
                   JOIN hostellers h ON c.hosteller_id = h.id 
                   ORDER BY c.to_datetime ASC 
                   LIMIT 3";
$checkouts_result = $conn->query($checkouts_query);

// Get maintenance requests where recipient is 'admin'
$maintenance_query = "SELECT r.*, h.id as hosteller_id, h.room, h.name as full_name 
                     FROM reports r 
                     JOIN hostellers h ON r.user_id = h.id 
                     WHERE r.recipient = 'admin'
                     ORDER BY r.created_at DESC 
                     LIMIT 3";
$maintenance_result = $conn->query($maintenance_query);

// Helper function to calculate days left
function daysLeft($date) {
    $checkout_date = new DateTime($date);
    $today = new DateTime('now');
    $interval = $today->diff($checkout_date);
    return $interval->days;
}

// Helper function to format date
function formatDate($date) {
    return date('d M Y', strtotime($date));
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/adstyle.css">
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
          <li class="manage"><img src="../images/Hosteller/Home/multiple-users-silhouette.png" class="reports-icon"> <span>Manage User Data</span></li>
          <li class="notification"><img src="../images/Hosteller/Home/notification.png" class="notification-icon"> <span> Manage Notifications</span></li>
          <li class="status"><img src="../images/Hosteller/Home/clipboard.png" class="status-icon"> <span>View room Status</span></li>
          <li class="payments"><img src="../images/Hosteller/Home/cash-payment.png" class="payments-icon"> <span>Fee Payments</span></li>
          <li class="reports"><img src="../images/Hosteller/Home/report.png" class="reports-icon"> <span>View Reports</span></li>
          <li class="transaction"><img src="../images/Hosteller/Home/settings.png" class="transaction-icon"> <span>View Transaction History</span></li>
      </ul>
      </nav>

      <div class="right-content">

        <div class="nav-bar">
          <div>
            <h1>Administrator Dashboard</h1>
          </div>
          <div class="nav-icon">
            <div><i class="fas fa-power-off menu-icon" id="logout-btn"></i></div>
            <div id="tooltip-user" class="tooltip-target"><i class="fas fa-user-circle menu-icon"></i></div>
          </div>
        </div>

        <div class="right-container">

          <h2>Welcome Administrator</h2>
          <!-- Content for the right-container -->
          <div class="dashboard-stats">
            <div class="stat-card">
              <div class="stat-icon"><i class="fas fa-bed"></i></div>
              <div class="stat-content">
                <h3>Total Rooms</h3>
                <p><?php echo $total_rooms; ?></p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fas fa-users"></i></div>
              <div class="stat-content">
                <h3>Occupants</h3>
                <p><?php echo $occupied; ?></p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fas fa-door-open"></i></div>
              <div class="stat-content">
                <h3>Vacant</h3>
                <p><?php echo $vacant; ?></p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
              <div class="stat-content">
                <h3>Issues</h3>
                <p><?php echo $issues; ?></p>
              </div>
            </div>
          </div>

          <div class="dashboard-sections">

            <div class="upcoming-checkouts">
              <div class="section-header">
                <h2>Upcoming Checkouts</h2>
                <?php //<button class="view-checkout-btn" onclick="location.href='adCheckouts.php'">View All</button> ?>
              </div>
              <div class="checkout-list">
                <?php if ($checkouts_result->num_rows > 0): ?>
                  <?php while($checkout = $checkouts_result->fetch_assoc()): ?>
                    <div class="checkout-item">
                      <div class="student-info">
                        <img src="../images/Hosteller/Reports/who.png" alt="Student Photo">
                        <div>
                          <h4><?php echo htmlspecialchars($checkout['full_name']); ?></h4>
                          <p>Room <?php echo htmlspecialchars($checkout['room']); ?></p>
                        </div>
                      </div>
                      <div class="checkout-date">
                        <p><?php echo formatDate($checkout['to_datetime']); ?></p>
                        <span class="days-left"><?php echo daysLeft($checkout['to_datetime']); ?> days left</span>
                      </div>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <p>No upcoming checkouts found.</p>
                <?php endif; ?>
              </div>
            </div>

            <div class="maintenance-requests">
              <div class="section-header">
                <h2>Reports and Complaints</h2>
                <button class="view-report-btn" onclick="location.href='adReport.php'">View All</button>
              </div>
              <div class="maintenance-list">
                <?php if ($maintenance_result->num_rows > 0): ?>
                  <?php while($report = $maintenance_result->fetch_assoc()): ?>
                    <div class="maintenance-item">
                      <div class="maintenance-info">
                        <div class="maintenance-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                          <h4><?php echo htmlspecialchars($report['complaint_type']); ?></h4>
                          <p>Room <?php echo htmlspecialchars($report['room']); ?> - <?php echo htmlspecialchars($report['subject']); ?></p>
                          <span class="request-date">Reported: <?php echo formatDate($report['created_at']); ?></span>
                        </div>
                      </div>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <p>No maintenance requests found.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
              <button onclick="location.href='adnotification.php'">Send Notification</button>
              <button onclick="location.href='adStatus.php'">Manage Rooms</button>
              <button onclick="location.href='adPayment.php'">View Payments</button>
              <button onclick="location.href='adReport.php'">Check Reports</button>
            </div>
          </div>          

        </div>
      </div>
    </div>

    <script src="scripts/adscript.js"></script>
  </body>
</html>
<?php
// Close the database connection
$conn->close();
?>