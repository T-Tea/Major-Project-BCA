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

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// If form is submitted to update amenities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_amenities'])) {
    $room_number = $_POST['room_number'];
    
    // Get values from form
    $lights_total = $_POST['lights_total'];
    $lights_working = $_POST['lights_working'];
    $lights_defective = $lights_total - $lights_working;
    
    $fans_total = $_POST['fans_total'];
    $fans_working = $_POST['fans_working'];
    $fans_defective = $fans_total - $fans_working;
    
    $doors_total = $_POST['doors_total'];
    $doors_working = $_POST['doors_working'];
    $doors_defective = $doors_total - $doors_working;
    
    $windows_total = $_POST['windows_total'];
    $windows_working = $_POST['windows_working'];
    $windows_defective = $windows_total - $windows_working;
    
    $power_outlets_total = $_POST['power_outlets_total'];
    $power_outlets_working = $_POST['power_outlets_working'];
    $power_outlets_defective = $power_outlets_total - $power_outlets_working;
    
    $solar_bulbs_total = $_POST['solar_bulbs_total'];
    $solar_bulbs_working = $_POST['solar_bulbs_working'];
    $solar_bulbs_defective = $solar_bulbs_total - $solar_bulbs_working;
    
    // Determine condition status based on defective items
    $is_defective = ($lights_defective > 0 || $fans_defective > 0 || $doors_defective > 0 || 
                    $windows_defective > 0 || $power_outlets_defective > 0 || $solar_bulbs_defective > 0);
    $condition_status = $is_defective ? 'defective' : 'working';
    
    // Update or insert into database
    $sql = "INSERT INTO amenities (
                room_number, 
                lights_total, lights_working, lights_defective,
                fans_total, fans_working, fans_defective,
                doors_total, doors_working, doors_defective,
                windows_total, windows_working, windows_defective,
                power_outlets_total, power_outlets_working, power_outlets_defective,
                solar_bulbs_total, solar_bulbs_working, solar_bulbs_defective,
                condition_status
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                lights_total = VALUES(lights_total),
                lights_working = VALUES(lights_working),
                lights_defective = VALUES(lights_defective),
                fans_total = VALUES(fans_total),
                fans_working = VALUES(fans_working),
                fans_defective = VALUES(fans_defective),
                doors_total = VALUES(doors_total),
                doors_working = VALUES(doors_working),
                doors_defective = VALUES(doors_defective),
                windows_total = VALUES(windows_total),
                windows_working = VALUES(windows_working),
                windows_defective = VALUES(windows_defective),
                power_outlets_total = VALUES(power_outlets_total),
                power_outlets_working = VALUES(power_outlets_working),
                power_outlets_defective = VALUES(power_outlets_defective),
                solar_bulbs_total = VALUES(solar_bulbs_total),
                solar_bulbs_working = VALUES(solar_bulbs_working),
                solar_bulbs_defective = VALUES(solar_bulbs_defective),
                condition_status = VALUES(condition_status)";
                
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "siiiiiiiiiiiiiiiiiii", 
        $room_number,
        $lights_total, $lights_working, $lights_defective,
        $fans_total, $fans_working, $fans_defective,
        $doors_total, $doors_working, $doors_defective,
        $windows_total, $windows_working, $windows_defective,
        $power_outlets_total, $power_outlets_working, $power_outlets_defective,
        $solar_bulbs_total, $solar_bulbs_working, $solar_bulbs_defective,
        $condition_status
    );
    
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        $message = "Room $room_number updated successfully";
    } else {
        $message = "Error updating room: " . $conn->error;
    }
}

// Get list of all rooms for dropdown
$room_sql = "SELECT DISTINCT room FROM hostellers ORDER BY room";
$room_result = $conn->query($room_sql);
$rooms = [];
while ($row = $room_result->fetch_assoc()) {
    $rooms[] = $row['room'];
}

// Get selected room's data
$selected_room = isset($_GET['room']) ? $_GET['room'] : (count($rooms) > 0 ? $rooms[0] : '');
$amenities_data = [];

if (!empty($selected_room)) {
    $amenities_sql = "SELECT * FROM amenities WHERE room_number = ?";
    $amenities_stmt = $conn->prepare($amenities_sql);
    $amenities_stmt->bind_param("s", $selected_room);
    $amenities_stmt->execute();
    $amenities_result = $amenities_stmt->get_result();
    
    if ($amenities_result->num_rows > 0) {
        $amenities_data = $amenities_result->fetch_assoc();
    } else {
        // Create default values
        $amenities_data = [
            'lights_total' => 0,
            'lights_working' => 0,
            'lights_defective' => 0,
            'fans_total' => 0,
            'fans_working' => 0,
            'fans_defective' => 0,
            'doors_total' => 0,
            'doors_working' => 0,
            'doors_defective' => 0,
            'windows_total' => 0,
            'windows_working' => 0,
            'windows_defective' => 0,
            'power_outlets_total' => 0,
            'power_outlets_working' => 0,
            'power_outlets_defective' => 0,
            'solar_bulbs_total' => 0,
            'solar_bulbs_working' => 0,
            'solar_bulbs_defective' => 0,
            'condition_status' => 'working'
        ];
    }
    
    if (isset($amenities_stmt)) {
        $amenities_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/adstyle.css">
    <link rel="stylesheet" href="styles/adStatus.css">
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
                <?php if (isset($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="status-content">
                    <p>Room Status</p>

                    <form method="GET" id="room-form">
                        <label for="room-select">Select Room:</label>
                        <select id="room-select" name="room" onchange="this.form.submit()">
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room); ?>" <?php echo ($selected_room == $room) ? 'selected' : ''; ?>>
                                    Room <?php echo htmlspecialchars($room); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <form method="POST" id="amenities-form">
                        <input type="hidden" name="room_number" value="<?php echo htmlspecialchars($selected_room); ?>">
                        
                        <div class="room-info">
                            <p><img src="../images/Admin/Adstatus/cactus.png" class="profile-pic"> Room: <span id="room-number"><?php echo htmlspecialchars($selected_room); ?></span></p>
                            <p>Condition: 
                                <span class="status-tag <?php echo ($amenities_data['condition_status'] == 'working') ? 'good' : 'bad'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($amenities_data['condition_status'])); ?>
                                </span>
                                <em>(Auto-calculated based on defective items)</em>
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
                                <td><img src="../images/Admin/Adstatus/light-bulb.png" class="amenity-icon"></td>
                                <td>Lights</td>
                                <td><input type="number" name="lights_total" value="<?php echo htmlspecialchars($amenities_data['lights_total']); ?>" min="0"></td>
                                <td><input type="number" name="lights_working" value="<?php echo htmlspecialchars($amenities_data['lights_working']); ?>" min="0"></td>
                                <td><?php echo htmlspecialchars($amenities_data['lights_defective']); ?></td>
                                <td>
                                    <?php echo ($amenities_data['lights_defective'] > 0) ? '❌ Defective' : '✅ Working'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="../images/Admin/Adstatus/fan.png" class="amenity-icon"></td>
                                <td>Fans</td>
                                <td><input type="number" name="fans_total" value="<?php echo htmlspecialchars($amenities_data['fans_total']); ?>" min="0"></td>
                                <td><input type="number" name="fans_working" value="<?php echo htmlspecialchars($amenities_data['fans_working']); ?>" min="0"></td>
                                <td><?php echo htmlspecialchars($amenities_data['fans_defective']); ?></td>
                                <td>
                                    <?php echo ($amenities_data['fans_defective'] > 0) ? '❌ Defective' : '✅ Working'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="../images/Admin/Adstatus/door.png" class="amenity-icon"></td>
                                <td>Doors</td>
                                <td><input type="number" name="doors_total" value="<?php echo htmlspecialchars($amenities_data['doors_total']); ?>" min="0"></td>
                                <td><input type="number" name="doors_working" value="<?php echo htmlspecialchars($amenities_data['doors_working']); ?>" min="0"></td>
                                <td><?php echo htmlspecialchars($amenities_data['doors_defective']); ?></td>
                                <td>
                                    <?php echo ($amenities_data['doors_defective'] > 0) ? '❌ Defective' : '✅ Working'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="../images/Admin/Adstatus/window.png" class="amenity-icon"></td>
                                <td>Windows</td>
                                <td><input type="number" name="windows_total" value="<?php echo htmlspecialchars($amenities_data['windows_total']); ?>" min="0"></td>
                                <td><input type="number" name="windows_working" value="<?php echo htmlspecialchars($amenities_data['windows_working']); ?>" min="0"></td>
                                <td><?php echo htmlspecialchars($amenities_data['windows_defective']); ?></td>
                                <td>
                                    <?php echo ($amenities_data['windows_defective'] > 0) ? '❌ Defective' : '✅ Working'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="../images/Admin/Adstatus/electric-outlet.png" class="amenity-icon"></td>
                                <td>Power Outlets</td>
                                <td><input type="number" name="power_outlets_total" value="<?php echo htmlspecialchars($amenities_data['power_outlets_total']); ?>" min="0"></td>
                                <td><input type="number" name="power_outlets_working" value="<?php echo htmlspecialchars($amenities_data['power_outlets_working']); ?>" min="0"></td>
                                <td><?php echo htmlspecialchars($amenities_data['lights_defective']); ?></td>
                                <td>
                                    <?php echo ($amenities_data['power_outlets_defective'] > 0) ? '❌ Defective' : '✅ Working'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="../images/Admin/Adstatus/power.png" class="amenity-icon"></td>
                                <td>Solar Bulb</td>
                                <td><input type="number" name="solar_bulbs_total" value="<?php echo htmlspecialchars($amenities_data['solar_bulbs_total']); ?>" min="0"></td>
                                <td><input type="number" name="solar_bulbs_working" value="<?php echo htmlspecialchars($amenities_data['solar_bulbs_working']); ?>" min="0"></td>
                                <td><?php echo htmlspecialchars($amenities_data['lights_defective']); ?></td>
                                <td>
                                    <?php echo ($amenities_data['solar_bulbs_defective'] > 0) ? '❌ Defective' : '✅ Working'; ?>
                                </td>
                            </tr>
                            
                            
                        </table>
                        
                        <div class="buttons">
                            <button type="submit" name="update_amenities" class="update-btn">Save Changes</button>
                            <button type="reset" class="reset-btn">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple validation to ensure working count doesn't exceed total
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('change', function() {
                const row = this.closest('tr');
                const totalInput = row.querySelector('input[name$="_total"]');
                const workingInput = row.querySelector('input[name$="_working"]');
                
                if (parseInt(workingInput.value) > parseInt(totalInput.value)) {
                    workingInput.value = totalInput.value;
                }
                
                const defectiveCell = row.querySelector('td:nth-child(5)');
                const defective = parseInt(totalInput.value) - parseInt(workingInput.value);
                defectiveCell.textContent = defective;
                
                const conditionCell = row.querySelector('td:nth-child(6)');
                conditionCell.textContent = defective > 0 ? '❌ Defective' : '✅ Working';
            });
        });
    </script>
    <script src="scripts/adscript.js"></script>
</body>
</html>