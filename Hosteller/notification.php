<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "hostel";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameter (if any)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Prepare SQL query based on filter
if ($filter == 'all') {
    $sql = "SELECT * FROM notifications ORDER BY date_sent DESC";
} else {
    $sql = "SELECT * FROM notifications WHERE sender_role = ? ORDER BY date_sent DESC";
}

// Prepare and execute statement
$stmt = $conn->prepare($sql);

if ($filter != 'all') {
    $stmt->bind_param("s", $filter);
}

$stmt->execute();
$result = $stmt->get_result();

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

// Function to check if file exists
function fileExists($filePath) {
    return !empty($filePath) && file_exists($filePath);
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hosteller dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
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
          <li class="status"><img src="../images/Hosteller/Home/clipboard.png" class="status-icon"> <span>Status</span></li>
          <li class="payments"><img src="../images/Hosteller/Home/cash-payment.png" class="payments-icon"> <span>Payments</span></li>
          <li class="checkout"><img src="../images/Hosteller/Home/logout.png" class="payments-icon"> <span>Request Checkout</span></li>
          <li class="reports"><img src="../images/Hosteller/Home/report.png" class="reports-icon"> <span>Reports</span></li>
          <li class="transaction"><img src="../images/Hosteller/Home/settings.png" class="transaction-icon"> <span>Transaction History</span></li>
      </ul>
      </nav>
    
      <div class="right-content">

        <div class="nav-bar">
          <div>
            <h1>Hosteller Dashboard</h1>
          </div>
          <div class="nav-icon">
            <div><i class="fas fa-power-off menu-icon" id="logout-btn"></i></div>
            <div id="tooltip-user" class="tooltip-target"><i class="fas fa-user-circle menu-icon"></i></div>
          </div>
        </div>

        <div class="right-container">
          <div class="notifications-container">
            <h2><img src="../images/Admin/Adnotification/notification (1).png" class="notif-icon"> Notifications</h2>
        
            <div class="filter-section">
                <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="filter-form">
                    <label for="filter">Filter by:</label>
                    <select id="filter" name="filter" onchange="this.form.submit()">
                        <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="warden" <?php echo $filter == 'warden' ? 'selected' : ''; ?>>Warden</option>
                        <option value="admin" <?php echo $filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </form>
            </div>
        
            <div class="notification-list">
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="notification-card">';
                        echo '<div class="notif-header">';
                        echo '<span class="notif-sender">From: ' . ucfirst($row["sender_role"]) . '</span>';
                        echo '<span class="notif-date">' . formatDate($row["date_sent"]) . '</span>';
                        echo '</div>';
                        echo '<h3 class="notif-title">' . htmlspecialchars($row["title"]) . '</h3>';
                        echo '<p class="notif-message">' . htmlspecialchars($row["description"]) . '</p>';
                        
                        // Check if there's an attachment
                        if (!empty($row["attachment"])) {
                            echo '<a href="attachments/' . htmlspecialchars($row["attachment"]) . '" class="notif-attachment" target="_blank">📎 View Attachment</a>';
                        }
                        
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-notifications">';
                    echo '<p>No notifications found.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>        

        </div>

      </div>

    </div>
    <script src="scripts/script.js"></script>
  </body>
</html>

<?php
// Close connection
$stmt->close();
$conn->close();
?>