<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New User</title>
    <style>
        /* Modern and clean styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="number"]:focus {
            border-color: #007BFF;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .return-button {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .return-button:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .warning {
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register New User</h1>

        <?php
        // Fetch the next available ID from get_next_id.php
        $nextId = file_get_contents("http://192.168.73.157/get_next_id.php"); // Replace with your server URL
        if (!$nextId) {
            echo '<p class="message error">Error fetching the next available ID.</p>';
            exit();
        }
        ?>

        <form id="registerForm">
            <label for="id">User ID (Auto-generated) 1-127:</label>
            <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($nextId); ?>" readonly required>

            <label for="nickname">Nickname:</label>
            <input type="text" id="nickname" name="nickname" required pattern="[A-Za-z\s]+" title="Only alphabets and spaces are allowed.">

            <label for="matrix_num">Matrix Number:</label>
            <input type="text" id="matrix_num" name="matrix_num" required pattern="[A-Za-z0-9]+" title="Only alphanumeric characters are allowed.">
            <div class="warning">
                <strong>Warning:</strong> Ensure the Matrix Number is accurate. Matrix Number cannot be edited after this.
            </div>

            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required title="Full Name can contain any characters.">

            <!-- Scan Fingerprint Button placed directly below Full Name textbox -->
            <button type="button" id="scanFingerprintButton">Scan Fingerprint and Register</button>
        </form>

        <div id="messageContainer"></div>

        <!-- Return Button -->
        <a href="index.php" class="return-button">Return to Home</a>
    </div>

    <script>
        // Function to validate input fields
        function validateInputs() {
            const id = document.getElementById('id').value;
            const nickname = document.getElementById('nickname').value;
            const matrixNum = document.getElementById('matrix_num').value;
            const fullName = document.getElementById('fullname').value;

            // Check if any field is empty
            if (!id || !nickname || !matrixNum || !fullName) {
                showMessage('error', 'All fields are required.');
                return false;
            }

            // Validate Nickname (only alphabets and spaces)
            const nicknameRegex = /^[A-Za-z\s]+$/;
            if (!nicknameRegex.test(nickname)) {
                showMessage('error', 'Nickname can only contain alphabets and spaces.');
                return false;
            }

            // Validate Matrix Number (only alphanumeric characters)
            const matrixNumRegex = /^[A-Za-z0-9]+$/;
            if (!matrixNumRegex.test(matrixNum)) {
                showMessage('error', 'Matrix Number can only contain alphanumeric characters.');
                return false;
            }

            return true;
        }

        // Function to check if Matrix Number already exists
        function checkMatrixNumber(matrixNum) {
            return fetch(`check_matrix.php?matrix_num=${matrixNum}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        showMessage('error', 'Matrix Number already exists.');
                        return false;
                    }
                    return true;
                })
                .catch(error => {
                    console.error("Error checking Matrix Number:", error);
                    showMessage('error', 'Error checking Matrix Number.');
                    return false;
                });
        }

        // Event listener for the "Scan Fingerprint and Register" button
        document.getElementById('scanFingerprintButton').addEventListener('click', async function() {
            if (!validateInputs()) {
                return; // Stop if validation fails
            }

            const matrixNum = document.getElementById('matrix_num').value;

            // Check if Matrix Number already exists
            const isMatrixUnique = await checkMatrixNumber(matrixNum);
            if (!isMatrixUnique) {
                return; // Stop if Matrix Number is not unique
            }

            // Trigger fingerprint scanning on the ESP32
            const formData = new FormData(document.getElementById('registerForm'));
            const nickname = formData.get('nickname');
            const fullName = formData.get('fullname');
            const userId = formData.get('id'); // Get the auto-generated user ID

            // Send the form data to the ESP32 for fingerprint scanning
            fetch('http://192.168.73.253/register', { // Replace with your ESP32 server URL
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${userId}&nickname=${nickname}&matrix_num=${matrixNum}&fullname=${fullName}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage('success', data.message);
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                showMessage('error', 'Error triggering fingerprint scan: ' + error.message);
            });
        });

        // Function to display messages
        function showMessage(type, message) {
            const messageContainer = document.getElementById('messageContainer');
            messageContainer.innerHTML = `<p class="message ${type}">${message}</p>`;
        }
    </script>
</body>
</html>