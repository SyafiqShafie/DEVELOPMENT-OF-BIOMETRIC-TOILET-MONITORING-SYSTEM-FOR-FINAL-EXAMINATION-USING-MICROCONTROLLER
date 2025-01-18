<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esp32_access";
$port = 3307;

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Check if all the necessary POST fields are set
if (!isset($_POST['id']) || !isset($_POST['nickname']) || !isset($_POST['matrix_num']) || !isset($_POST['fullname'])) {
    echo json_encode(["status" => "error", "message" => "All fields (id, nickname, matrix_num, fullname) are required"]);
    exit();
}

// Retrieve POST data
$id = $_POST['id'];
$nickname = $_POST['nickname'];
$matrix_num = $_POST['matrix_num'];
$fullname = $_POST['fullname'];

// Validate input
if (empty($id) || !filter_var($id, FILTER_VALIDATE_INT)) {
    echo json_encode(["status" => "error", "message" => "ID must be a valid integer"]);
    exit();
}
if (empty($matrix_num) || !preg_match("/^[a-zA-Z0-9]+$/", $matrix_num)) {
    echo json_encode(["status" => "error", "message" => "Matrix number must be alphanumeric"]);
    exit();
}
if (empty($nickname)) {
    echo json_encode(["status" => "error", "message" => "Nickname is required"]);
    exit();
}

// Check if the Matrix Number already exists in the database
$check_matrix_sql = "SELECT matrix_num FROM users WHERE matrix_num = ?";
$check_stmt = $conn->prepare($check_matrix_sql);
$check_stmt->bind_param("s", $matrix_num);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // Matrix Number already exists
    echo json_encode(["status" => "error", "message" => "Duplicate entry: Matrix Number already exists"]);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO users (id, nickname, matrix_num, fullname) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $id, $nickname, $matrix_num, $fullname);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "New record created successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
?>