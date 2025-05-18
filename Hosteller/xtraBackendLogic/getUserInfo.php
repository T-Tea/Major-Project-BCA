<?php
session_start();

// === Direct Database Connection Here ===
$servername = "localhost"; // or whatever you use
$username = "root";
$password = "";
$database = "hostel";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// === Main Logic ===
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role']; // 'admin', 'warden', or 'hosteller'

// Select correct table
switch ($role) {
    case 'admin':
        $table = 'admin';
        break;
    case 'hosteller':
        $table = 'hostellers';
        break;
    case 'warden':
        $table = 'wardens';
        break;
    default:
        echo json_encode(["error" => "Unknown role"]);
        exit;
}

// Query to fetch user's name
$stmt = $conn->prepare("SELECT name FROM $table WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "name" => $row['name'],
        "role" => $role
    ]);
} else {
    echo json_encode(["error" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
