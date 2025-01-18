<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "esp32_access";
$port = 3307; // Your MySQL port

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the maximum ID limit
$max_id_limit = 127;

// SQL query to find the next available ID within the range 1 to 127
$sql = "SELECT id FROM users WHERE id BETWEEN 1 AND $max_id_limit ORDER BY id ASC";
$result = $conn->query($sql);

if ($result) {
    $expected_id = 1; // Start with the first ID
    $next_available_id = null; // Variable to store the next available ID

    // Iterate through the result set
    while ($row = $result->fetch_assoc()) {
        if ($row['id'] == $expected_id) {
            // If the ID matches the expected ID, move to the next ID
            $expected_id++;
        } else {
            // If there's a gap, set the next available ID
            $next_available_id = $expected_id;
            break;
        }
    }

    // If no gaps were found, check if the expected ID is within the limit
    if ($next_available_id === null && $expected_id <= $max_id_limit) {
        $next_available_id = $expected_id;
    }

    // Output the next available ID or an error message if all IDs are used
    if ($next_available_id !== null) {
        echo $next_available_id;
    } else {
        echo "All IDs (1 to 127) are already used.";
    }
} else {
    // If the query fails, output an error message
    echo "Error retrieving next ID.";
}

// Close the connection
$conn->close();
?>