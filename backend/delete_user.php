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

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Get the user ID from the request
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    die(json_encode(["success" => false, "message" => "Invalid user ID."]));
}

// Prepare and execute the delete query
$query = $conn->prepare("DELETE FROM users WHERE id = ?");
if (!$query) {
    die(json_encode(["success" => false, "message" => "Failed to prepare query: " . $conn->error]));
}

$query->bind_param("i", $userId);

if (!$query->execute()) {
    die(json_encode(["success" => false, "message" => "Failed to execute query: " . $query->error]));
}

// Check if any rows were affected
if ($query->affected_rows > 0) {
    // User deleted successfully, now delete the fingerprint from the ESP32
    $esp32Url = "http://192.168.73.253/delete_fingerprint"; // Replace with your ESP32's IP
    $postData = http_build_query(["id" => $userId]);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $esp32Url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);
    curl_close($ch);

    // Check if the ESP32 responded successfully
    if ($response === false) {
        echo json_encode(["success" => false, "message" => "User deleted, but failed to delete fingerprint from ESP32."]);
    } else {
        echo json_encode(["success" => true, "message" => "User and fingerprint deleted successfully."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found or already deleted."]);
}

$query->close();
$conn->close();
?>