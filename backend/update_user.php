<?php
// Set PHP timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esp32_access";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Get form data
$userId = $_POST['id'];
$fullname = $_POST['fullname'];

// Update the user's full name
$query = $conn->prepare("UPDATE users SET fullname = ? WHERE id = ?");
$query->bind_param("si", $fullname, $userId);
$query->execute();

if ($query->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "User's full name updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "No changes were made or user not found."]);
}

$query->close();
$conn->close();
?>