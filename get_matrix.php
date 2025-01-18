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
    die("Connection failed: " . $conn->connect_error);
}

// Get the user ID from the GET request
$userID = $_GET['id'];

// Retrieve the matrix number associated with the user ID
$sql = "SELECT matrix_num FROM users WHERE id = '$userID'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the matrix number if found
    $row = $result->fetch_assoc();
    echo $row['matrix_num'];
} else {
    echo "User not found";
}

$conn->close();
?>
