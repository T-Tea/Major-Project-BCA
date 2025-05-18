<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hostel");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$role = $_POST['role'];
$username = $_POST['username'];
$password = $_POST['password'];

if (empty($role) || empty($username) || empty($password)) {
    echo "All fields are required.";
    exit;
}

$tableMap = [
    "hosteller" => "hostellers",
    "warden"    => "warden",  // Assuming future table
    "admin"     => "admin"    // Assuming future table
];

if (!array_key_exists($role, $tableMap)) {
    echo "Invalid role selected.";
    exit;
}

$table = $tableMap[$role];

// Check if user exists
$sql = "SELECT * FROM $table WHERE name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role'] = $role;

        // Redirect based on role
        switch ($role) {
            case "hosteller":
                header("Location: ../Hosteller/home.php");
                break;
            case "warden":
                header("Location: ../Warden/wHome.php");
                break;
            case "admin":
                header("Location: ../Admin/adHome.php");
                break;
        }
        exit;
    } else {
        echo "Invalid password.";
    }
} else {
    echo "User not found.";
}

$stmt->close();
$conn->close();
?>
