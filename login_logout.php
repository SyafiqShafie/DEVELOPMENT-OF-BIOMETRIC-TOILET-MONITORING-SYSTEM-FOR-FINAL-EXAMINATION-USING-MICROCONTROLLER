<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esp32_access";
$port = 3307;

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Get action type and matrix_num from POST request
$action = $_POST['action'] ?? '';
$matrix_num = $_POST['matrix_num'] ?? '';

if (empty($action) || empty($matrix_num)) {
    echo json_encode(["status" => "error", "message" => "Invalid request parameters."]);
    exit;
}

// Get current timestamp
$currentTime = date("Y-m-d H:i:s");

if ($action === "login") {
    // Perform login operation (Insert a new row for login)
    $sql = "INSERT INTO user_log (matrix_num, login) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $matrix_num, $currentTime);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Login time recorded."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Matrix number not found or login failed."]);
    }

    $stmt->close();
} elseif ($action === "logout") {
    // Perform logout operation (Update the logout timestamp)
    $sql = "UPDATE user_log SET logout = ? WHERE matrix_num = ? AND logout IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $currentTime, $matrix_num);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Logout time recorded."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Matrix number not found or already logged out."]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action specified."]);
}

// Close connection
$conn->close();
?>
