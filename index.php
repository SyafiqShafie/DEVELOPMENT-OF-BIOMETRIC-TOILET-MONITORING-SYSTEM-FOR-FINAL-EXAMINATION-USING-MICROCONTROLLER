<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Restroom Access Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: white; /* White background */
            color: #333; /* Dark text */
        }
        h1 {
            color: #00008B; /* Dark blue accent */
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #00008B; /* Dark blue accent */
            margin-bottom: 10px;
        }
        table {
            width: 50%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
            box-sizing: border-box;
        }
        th {
            background-color: #00008B; /* Dark blue header */
            color: white;
            transition: background-color 0.3s ease; /* Header hover animation */
        }
        th:hover {
            background-color: #000080; /* Darker blue on hover */
        }
        tr:nth-child(even) {
            background-color: #f9f9f9; /* Alternate row color */
        }
        tr:hover {
            background-color: #f1f1f1; /* Hover effect */
        }
        tr {
            animation: fadeIn 0.5s ease-in-out; /* Row fade-in animation */
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .form-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Align to the left */
        }
        .delete-button, .edit-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            transition: transform 0.3s ease; /* Button hover animation */
        }
        .edit-button {
            background-color: #28a745;
        }
        .delete-button:hover {
            background-color: #c82333;
            transform: scale(1.1); /* Scale effect on hover */
        }
        .edit-button:hover {
            background-color: #218838;
            transform: scale(1.1); /* Scale effect on hover */
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: #333; /* Dark text */
            animation: slideDown 0.3s ease-in-out; /* Modal slide-down animation */
        }
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s ease; /* Close button hover animation */
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        /* Floating Button Styles */
        .floating-button {
            position: fixed;
            padding: 15px 25px;
            background-color: #00008B; /* Dark blue button */
            color: white;
            text-decoration: none;
            border-radius: 50px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            animation: float 3s ease-in-out infinite; /* Floating animation */
        }
        .floating-button:hover {
            background-color: #000080; /* Darker blue on hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }
        /* Floating Animation */
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        /* Navigation Buttons */
        .nav-buttons {
            text-align: left; /* Align to the left */
            margin-top: 20px;
        }
        .nav-button {
            padding: 10px 20px;
            background-color: #00008B; /* Dark blue button */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            font-size: 16px;
            transition: transform 0.3s ease; /* Button hover animation */
        }
        .nav-button:hover {
            background-color: #000080; /* Darker blue on hover */
            transform: scale(1.1); /* Scale effect on hover */
        }
        .nav-button.active {
            background-color: #000080; /* Darker blue for active button */
        }
        /* Hide tables by default */
        .table-container {
            display: none;
        }
        .table-container.active {
            display: block;
        }
    </style>
</head>
<body>
    <h1>Toilet Access Management System</h1>

    <?php 
    // Set PHP timezone to Malaysia
    date_default_timezone_set('Asia/Kuala_Lumpur');

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "esp32_access";
    $port = 3307;

    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    // Check connection
    if ($conn->connect_error) {
        die("<p style='color:red;'>Connection failed: " . $conn->connect_error . "</p>");
    }

    // Debug: Show server time in DD/MM/YYYY format
    echo "<p id='server-time'>Server Time (PHP): " . date('d/m/Y H:i:s') . "</p>";

    // Fetch users
    $usersQuery = "SELECT id, matrix_num, fullname FROM users";
    $result = $conn->query($usersQuery);

    if ($result->num_rows > 0) {
        echo "<div class='table-container active' id='users-table'>";
        echo "<h2>Users Table</h2>";
        echo "<table>
                <tr>
                    <th>No</th>
                    <th>ID</th>
                    <th>Matrix Number</th>
                    <th>Full Name</th>
                    <th>Action</th>
                </tr>";
        
        // Counter for row number
        $rowNumber = 1;

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $rowNumber . "</td>
                    <td>" . htmlspecialchars($row['id']) . "</td>
                    <td>" . htmlspecialchars($row['matrix_num']) . "</td>
                    <td>" . htmlspecialchars($row['fullname']) . "</td>
                    <td>
                        <button class='edit-button' data-id='" . $row['id'] . "' data-fullname='" . $row['fullname'] . "'>Edit</button>
                        <button class='delete-button' data-id='" . $row['id'] . "'>Delete</button>
                    </td>
                  </tr>";
            $rowNumber++; // Increment row number
        }
        echo "</table>";
        echo "</div>"; // Close users-table
    } else {
        echo "<p>No users found.</p>";
    }
    ?>

    <!-- User Log Table -->
    <div class="table-container" id="user-logs-table">
        <h2>User Log Table</h2>
        <!-- Date Filter Form -->
        <div class="form-container">
            <label for="date">Select a date to view user logs:</label>
            <input type="date" id="date" value="<?= date('Y-m-d') ?>">
            <button type="submit" onclick="fetchLogs();">View Logs</button>
        </div>
        <table id="logs-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Matrix Number</th>
                    <th>Full Name</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <!-- Logs will be dynamically loaded here -->
            </tbody>
        </table>
    </div>

    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <button class="nav-button active" onclick="showTable('users-table')">Users Table</button>
        <button class="nav-button" onclick="showTable('user-logs-table')">User Logs Table</button>
    </div>

    <!-- Floating Buttons -->
    <a href="register_form.php" class="floating-button" style="bottom: 20px; right: 20px;">Register New User</a>
    <button id="export-csv-button" class="floating-button" style="bottom: 90px; right: 20px;">Export to Excel</button>

    <!-- Edit User Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit User</h3>
            <form id="edit-form">
                <input type="hidden" id="edit-id" name="id">
                <label for="edit-fullname">Full Name:</label>
                <input type="text" id="edit-fullname" name="fullname" required>
                <br><br>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Helper function to format date to DD/MM/YYYY
        function formatDateToDMY(date) {
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            return new Intl.DateTimeFormat('en-GB', options).format(date);
        }

        // Fetch logs from the server
        function fetchLogs() {
            const dateInput = document.getElementById('date');
            const selectedDate = dateInput.value;

            fetch(`fetch_logs.php?date=${selectedDate}`)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.querySelector("#logs-table tbody");
                    tableBody.innerHTML = ""; // Clear current rows

                    if (data.length > 0) {
                        data.forEach(row => {
                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${row.no}</td>
                                <td>${row.matrix_num}</td>
                                <td>${row.fullname}</td>
                                <td>${row.login_time}</td>
                                <td>${row.logout_time}</td>
                                <td>${row.duration}</td>
                            `;
                            tableBody.appendChild(tr);
                        });
                    } else {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `<td colspan="6">No logs found for the selected date.</td>`;
                        tableBody.appendChild(tr);
                    }
                })
                .catch(error => console.error("Error fetching logs:", error));
        }

        // Keep server time visible and formatted as DD/MM/YYYY
        function refreshServerTime() {
            const serverTime = document.getElementById("server-time");
            const currentTime = new Date(); // JavaScript client-side time
            const formattedTime = `${formatDateToDMY(currentTime)} ${currentTime.toLocaleTimeString('en-GB')}`;
            serverTime.innerHTML = `Server Time (PHP): ${formattedTime}`;
        }

        // Delete user functionality
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this user?')) {
                    fetch(`delete_user.php?id=${userId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('User deleted successfully!');
                                location.reload(); // Reload the page to reflect changes
                            } else {
                                alert('Error deleting user: ' + data.message);
                            }
                        })
                        .catch(error => console.error("Error deleting user:", error));
                }
            });
        });

        // Edit user functionality
        const editModal = document.getElementById('edit-modal');
        const editForm = document.getElementById('edit-form');
        const editId = document.getElementById('edit-id');
        const editFullname = document.getElementById('edit-fullname');
        const closeModal = document.querySelector('.close');

        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userFullname = this.getAttribute('data-fullname');

                // Populate the form with current data
                editId.value = userId;
                editFullname.value = userFullname;

                // Show the modal
                editModal.style.display = 'block';
            });
        });

        // Close the modal
        closeModal.addEventListener('click', function() {
            editModal.style.display = 'none';
        });

        // Handle form submission
        editForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(editForm);
            fetch('update_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully!');
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error updating user: ' + data.message);
                }
            })
            .catch(error => console.error("Error updating user:", error));
        });

        // Export to CSV functionality
        document.getElementById('export-csv-button').addEventListener('click', function() {
            const table = document.getElementById('logs-table');
            const rows = table.querySelectorAll('tbody tr');
            const dateInput = document.getElementById('date');
            const selectedDate = dateInput.value;
            const formattedDate = selectedDate.replace(/-/g, '_');
            const fileName = `userlog_${formattedDate}.csv`;
            let csvContent = [];
            const headers = Array.from(table.querySelectorAll('thead th')).map(header => header.innerText);
            csvContent.push(headers.join(','));
            rows.forEach(row => {
                const rowData = Array.from(row.querySelectorAll('td')).map(cell => cell.innerText);
                csvContent.push(rowData.join(','));
            });
            const csvData = csvContent.join('\n');
            const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Show the selected table and hide the other
        function showTable(tableId) {
            const tables = document.querySelectorAll('.table-container');
            tables.forEach(table => {
                if (table.id === tableId) {
                    table.classList.add('active');
                } else {
                    table.classList.remove('active');
                }
            });

            // Update active button
            const buttons = document.querySelectorAll('.nav-button');
            buttons.forEach(button => {
                if (button.textContent.toLowerCase().includes(tableId.replace('-', ' '))) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Fetch logs initially and refresh every 10 seconds
        fetchLogs();
        setInterval(fetchLogs, 10000);

        // Update server time every second (simulation)
        setInterval(refreshServerTime, 1000);
    </script>
</body>
</html>