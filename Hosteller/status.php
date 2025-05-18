<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "hostel";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM hostellers WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $room_number = $user_data['room'];
    
    // Get amenities for this room
    $amenities_sql = "SELECT * FROM amenities WHERE room_number = ?";
    $amenities_stmt = $conn->prepare($amenities_sql);
    $amenities_stmt->bind_param("s", $room_number);
    $amenities_stmt->execute();
    $amenities_result = $amenities_stmt->get_result();
    
    // If no amenities found, create default entry
    if ($amenities_result->num_rows === 0) {
        $create_sql = "INSERT INTO amenities (room_number) VALUES (?)";
        $create_stmt = $conn->prepare($create_sql);
        $create_stmt->bind_param("s", $room_number);
        $create_stmt->execute();
        $create_stmt->close();
        
        // Fetch the new entry
        $amenities_stmt->execute();
        $amenities_result = $amenities_stmt->get_result();
    }
    
    $amenities_data = $amenities_result->fetch_assoc();
} else {
    // User not found
    header("Location: login.php");
    exit;
}

// Close statements
$user_stmt->close();
$amenities_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
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
                <div class="status-content">
                    <p>Room Status</p>
                  
                    <div class="room-info">
                        <p><img src="../images/Hosteller/Home/cactus.png" class="statusprofile-pic"> Room: <?php echo htmlspecialchars($room_number); ?></p>
                        <p>Condition: 
                            <span class="status-tag <?php echo ($amenities_data['condition_status'] == 'working') ? 'good' : 'bad'; ?>">
                                <?php echo ucfirst(htmlspecialchars($amenities_data['condition_status'])); ?>
                            </span>
                        </p>
                    </div>
              
                    <p>Amenities Status</p>
                    <table>
                        <tr>
                            <th></th>
                            <th>Amenity</th>
                            <th>Amount</th>
                            <th>Working</th>
                            <th>Defective</th>
                            <th>Condition</th>
                        </tr>
                        <tr>
                            <td><img src="../images/Hosteller/Status/light-bulb.png" class="amenity-icon"></td>
                            <td>Lights</td>
                            <td><?php echo htmlspecialchars($amenities_data['lights_total']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['lights_working']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['lights_defective']); ?></td>
                            <td><?php echo ($amenities_data['lights_defective'] > 0) ? '❌' : '✅'; ?></td>
                        </tr>
                        <tr>
                            <td><img src="../images/Hosteller/Status/fan.png" class="amenity-icon"></td>
                            <td>Fans</td>
                            <td><?php echo htmlspecialchars($amenities_data['fans_total']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['fans_working']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['fans_defective']); ?></td>
                            <td><?php echo ($amenities_data['fans_defective'] > 0) ? '❌' : '✅'; ?></td>
                        </tr>
                        <tr>
                            <td><img src="../images/Hosteller/Status/door.png" class="amenity-icon"></td>
                            <td>Door</td>
                            <td><?php echo htmlspecialchars($amenities_data['doors_total']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['doors_working']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['doors_defective']); ?></td>
                            <td><?php echo ($amenities_data['doors_defective'] > 0) ? '❌' : '✅'; ?></td>
                        </tr>
                        <tr>
                            <td><img src="../images/Hosteller/Status/window.png" class="amenity-icon"></td>
                            <td>Windows</td>
                            <td><?php echo htmlspecialchars($amenities_data['windows_total']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['windows_working']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['windows_defective']); ?></td>
                            <td><?php echo ($amenities_data['windows_defective'] > 0) ? '❌' : '✅'; ?></td>
                        </tr>
                        <tr>
                            <td><img src="../images/Hosteller/Status/electric-outlet.png" class="amenity-icon"></td>
                            <td>Power Outlet</td>
                            <td><?php echo htmlspecialchars($amenities_data['power_outlets_total']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['power_outlets_working']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['power_outlets_defective']); ?></td>
                            <td><?php echo ($amenities_data['power_outlets_defective'] > 0) ? '❌' : '✅'; ?></td>
                        </tr>
                        <tr>
                            <td><img src="../images/Hosteller/Status/power.png" class="amenity-icon"></td>
                            <td>Solar Bulb</td>
                            <td><?php echo htmlspecialchars($amenities_data['solar_bulbs_total']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['solar_bulbs_working']); ?></td>
                            <td><?php echo htmlspecialchars($amenities_data['solar_bulbs_defective']); ?></td>
                            <td><?php echo ($amenities_data['solar_bulbs_defective'] > 0) ? '❌' : '✅'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="scripts/script.js"></script>
</body>
</html>