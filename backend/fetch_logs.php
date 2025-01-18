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
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get the selected date from the request or default to today
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch logs for the selected date and reset row number per day
$query = $conn->prepare("
    SELECT 
        @row_number := @row_number + 1 AS no,  -- Assign row number that resets daily
        l.matrix_num, 
        u.fullname, 
        TIME(CONVERT_TZ(l.login, '+00:00', '+07:00')) AS login_time, 
        TIME(CONVERT_TZ(l.logout, '+00:00', '+07:00')) AS logout_time
    FROM user_log l
    JOIN users u ON l.matrix_num = u.matrix_num
    CROSS JOIN (SELECT @row_number := 0) AS init  -- Initialize the row_number variable
    WHERE DATE(l.login) = ? OR DATE(l.logout) = ?
    ORDER BY l.login;  -- Order by login time or any other suitable field
");

$query->bind_param("ss", $selectedDate, $selectedDate);
$query->execute();

$result = $query->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    // Check if logout_time is null
    if ($row['logout_time'] === null) {
        $row['duration'] = null; // Set duration to null if logout_time is null
    } else {
        // Calculate duration
        $loginTime = new DateTime($row['login_time']);
        $logoutTime = new DateTime($row['logout_time']);

        // Handle case where logout time is earlier than login time (e.g., across midnight)
        if ($logoutTime < $loginTime) {
            $logoutTime->modify('+1 day'); // Add 24 hours to logout time
        }

        // Calculate the duration
        $interval = $loginTime->diff($logoutTime);
        $row['duration'] = $interval->format('%H:%I:%S'); // Format as HH:MM:SS
    }

    // Add the modified row to the data array
    $data[] = $row;
}

// Output JSON response
header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>