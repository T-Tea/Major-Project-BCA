<?php
// Start session to access login information
session_start();

// Check if user is logged in, if not redirect to login page
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // replace with your db username
$password = ""; // replace with your db password
$dbname = "hostel"; // replace with your db name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in user's information
$user_id = $_SESSION['user_id'];

// Fetch the logged-in user's details
$sql = "SELECT * FROM hostellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Get the room number of the logged-in user
$room_number = $user_data['room'];
$building = $user_data['building'];

// Fetch all residents with the same room number
$sql = "SELECT * FROM hostellers WHERE room = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $room_number);
$stmt->execute();
$result = $stmt->get_result();

// Initialize residents array
$residents = [];
while($row = $result->fetch_assoc()) {
    $residents[] = $row;
}

// Set default values for resident 2 if there's only one resident
$resident1 = isset($residents[0]) ? $residents[0] : [
    'name' => 'NIL',
    'course' => 'NIL',
    'semester' => 'NIL',
    'phone_number' => 'NIL',
    'room' => 'NIL',
    'academic_year' => 'NIL'
];

$resident2 = isset($residents[1]) ? $residents[1] : [
    'name' => 'NIL',
    'course' => 'NIL',
    'semester' => 'NIL',
    'phone_number' => 'NIL',
    'room' => 'NIL',
    'academic_year' => 'NIL'
];

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
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
        <p class="home-titles">Your Profile</p>
          <div class="resident-profile">
            <div class="resident1">
            <div><img src="../images/Hosteller/Home/cactus.png" class="profile-pic"></div>
            <div class="user-info">
              <div class="user-sec1">
                <p><span class="username"><?php echo htmlspecialchars($resident1['name']); ?></span></p>
                <p><?php echo htmlspecialchars($resident1['course']); ?></p>
                <p><?php echo htmlspecialchars($resident1['semester']); ?> </p>
              </div>
              <div>
                <p>Phone: <?php echo htmlspecialchars($resident1['phone_number']); ?></p>
                <p>Room: <?php echo htmlspecialchars($resident1['room']); ?></p>
                <p><!--<?php echo htmlspecialchars($resident1['academic_year']); ?>-->2024 - 2025</p>
              </div>
              </div>
            </div>
            <div class="resident2">
            <div><img src="../images/Hosteller/Home/cactus.png" class="profile-pic"></div>
            <div class="user-info">
              <div class="user-sec1">
                <p><span class="username"><?php echo htmlspecialchars($resident2['name']); ?></span></p>
                <p><?php echo htmlspecialchars($resident2['course']); ?></p>
                <p><?php echo htmlspecialchars($resident2['semester']); ?> </p>
              </div>
              <div>
                <p>Phone: <?php echo htmlspecialchars($resident2['phone_number']); ?></p>
                <p>Room: <?php echo htmlspecialchars($resident2['room']); ?></p>
                <p><!--<?php echo htmlspecialchars($resident2['academic_year']); ?>-->2024 - 2025</p>
              </div>
              </div>
            </div>
          </div>

          <p class="home-titles">Other stats and infos</p>
          <div class="feature-cards">
            <div class="fcard1">
              <div><img src="../images/Hosteller/Home/plant.png" class="card-icon"></div>
              <div><p>Room Number: <?php echo htmlspecialchars($user_data['room']); ?></p></div>
            </div>
            <div class="fcard2">
              <div><img src="../images/Hosteller/Home/apartment.png" class="card-icon"></div>
              <div><p>Building: <?php echo htmlspecialchars($building); ?></p></div>
            </div>
            <div class="fcard3">
              <div><img src="../images/Hosteller/Home/apartment.png" class="card-icon"></div>
              <div>
                  <?php
                  // You can implement event logic here
                  // For now, displaying a static value
                  ?>
                  <p>Upcoming Event: 0</p>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
    
    <script src="scripts/script.js"></script>
  </body>
</html>