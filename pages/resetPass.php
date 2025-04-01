<?php
session_start();
include('../component/popupmsg.php'); // Include the function file
displayPopupMessage(); // Call the function to display any messages

// Initialize error messages
$error_message = ''; // Initialize to avoid undefined variable warnings
$password_error_message = $_SESSION['password_error'] ?? ''; // Retrieve password-related error messages if set

// Get the token from the URL
$token = $_GET['token'] ?? null;

// Check if token is missing or invalid
if (empty($token)) {
    $error_message = "Session expired. Please check the URL or request a new password reset.";
} else {
    // Debugging: Check if the token is being passed correctly
    echo "<script>console.log('Received Token: " . htmlspecialchars($token) . "');</script>";

    // Database connection and token validation
    include('../server/connect.php');

    // Validate the token by checking if it exists in either table
    $stmt = $conn->prepare("SELECT * FROM admintb WHERE reset_token = :token AND reset_expires > :current_time");
    $stmt->execute(['token' => $token, 'current_time' => time()]);
    $user = $stmt->fetch();

    // If no user found in admintb, check brgystaffinfotb table
    if (!$user) {
        $stmt = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE reset_token = :token AND reset_expires > :current_time");
        $stmt->execute(['token' => $token, 'current_time' => time()]);
        $user = $stmt->fetch();
    }

    if (!$user) {
        // Token expired or invalid
        $_SESSION['error'] = "The password reset link has expired or is invalid. Please request a new one.";
        header("Location: ../pages/sendInstruction.php");
        exit();
    } else {
        // Token is valid, user can reset their password
        echo "<script>console.log('Valid token received. You can reset your password.');</script>";
    }
}

unset($_SESSION['error']); // Clear error message after displaying
unset($_SESSION['password_error']); // Clear the password error after displaying
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Your existing CSS styles */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: url("../img/Stay informed, stay prepared.png");
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .reset-password-container {
            background: rgba(255, 255, 255, 0.6); /* White with 60% opacity */
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            width: 90%;
            max-width: 380px;
        }

        .reset-password-container h2 {
            font-size: 2em;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 50px;
            padding: 30px 0px 0px 0px;
            text-align: left;
        }

        .reset-password-container h2 span:first-child {
            color: #1F1F29; /* Color for "Change" */
        }

        .reset-password-container h2 span:last-child {
            color: #2B3467; /* Color for "password." */
        }

        .reset-password-container p {
            font-size: 0.90em;
            font-weight: 500;
            color: #1F1F29;
            padding: 0px 10px 0px 0px;
            text-align: left;
        }

        .reset-password-container label {
            font-size: 0.90em; /* Updated font size */
            font-weight: 600;
            text-align: left; /* Align label text to the left */
            display: block;
            margin-top: 10px;
            color: #1F1F29;
        }

        .reset-password-container input {
            width: 100%;
            padding: 10px;
            margin: 0px 10px 5px 0px;
            border: 2px solid #bdc3c7;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.90em;
            box-sizing: border-box;
        }

        .reset-password-container input:hover {
            background-color: #e9ecef;
        }

        .reset-password-container input:focus {
            outline: none; /* Remove the default browser outline */
            border: 2px solid #2B3467; /* Highlighted border */
            background-color: #f9f9f9; /* Slightly different background for focus */
        }

        .reset-password-container button {
            width: 100%;
            padding: 13px;
            background-color: #2B3467;
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            box-sizing: border-box;
            margin-top: 20px;
        }

        .reset-password-container button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .reset-password-container button:hover:not(:disabled) {
            background-color: #1F2947;
        }

        .reset-password-container a {
            display: block;
            margin-top: 5px;
            margin-bottom: 20px;
            color: #2B3467;
            text-decoration: none;
            text-align: right;
            font-size: .70em;
            font-weight: 600;
            text-decoration: underline;
        }

        .reset-password-container a:hover {
            text-decoration: underline;
            color: #1F1F29;
        }

        .account-link {
            display: flex; /* Flexbox for alignment */
            justify-content: center; /* Center horizontally */
            align-items: baseline; /* Align vertically */
            font-size: 0.70em; /* Set font size for both text and link */
            color: #1F1F29; /* Set the color for the regular text */
            margin-top: 20px; /* Add some spacing above */
            font-weight: 500; /* Slightly bold for the regular text */
        }

        .account-link span {
            font-size: inherit; /* Same size as parent */
            color: inherit; /* Regular text color */
        }

        .account-link a {
            font-size: inherit; /* Same size as parent */
            font-weight: 600;
            color: #2B3467; /* Link color */
            text-decoration: none; /* Remove underline */
            margin-left: 5px; /* Add spacing between text and link */
        }

        .account-link a:hover {
            text-decoration: underline; /* Add underline on hover */
        }

        .password-note {
            font-size: 0.8em;
            color: #555;
            margin-top: 20px;
            text-align: left;
        }

        .password-note li {
            color: green;
        }

        .popup-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9em;
            z-index: 10000;
            display: none; /* Hidden by default */
        }

        .popup-message.success {
            background-color: #4CAF50; /* Green for success */
        }

        .popup-message.error {
            background-color: #f44336; /* Red for error */
        }

        .error-message-container {
            color: red;
            margin-bottom: 10px;
        }

        /* Media Queries */
        @media (max-width: 1366px) {
            .reset-password-container {
                width: 150%;
                padding: 20px 30px;
            }

            .reset-password-container h2 {
                font-size: 2em;
            }

            .reset-password-container p {
                font-size: 0.9em;
            }

            .reset-password-container input {
                font-size: 0.9em;
            }

            .reset-password-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }

        @media (max-width: 768px) {
            .reset-password-container {
                width: 95%;
                padding: 20px 30px;
            }

            .reset-password-container h2 {
                font-size: 1.5em;
            }

            .reset-password-container p {
                font-size: 0.9em;
            }

            .reset-password-container input {
                font-size: 0.9em;
            }

            .reset-password-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }

        @media (max-width: 480px) {
            .reset-password-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .reset-password-container h2 {
                font-size: 1.2em;
            }

            .reset-password-container p {
                font-size: 0.8em;
            }

            .account-link {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .account-link a {
                margin-left: 0;
                margin-top: 5px;
            }
        }

        @media (max-width: 200px) {
            .reset-password-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .reset-password-container h2 {
                font-size: 1em;
            }

            .reset-password-container p {
                font-size: 0.6em;
            }

            .account-link {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .account-link a {
                margin-left: 0;
                margin-top: 5px;
            }
        }

        h2.password-rules {
            font-size: 0.7em;
            margin: 0; /* Remove all margins */
            padding: 0; /* Remove any potential padding */
            font-weight: 600;
            color: #1F1F29;
            margin-top: 29px;
            margin-bottom: 0;
        }

        p.password-rules2 {
            font-size: 0.6em;
            margin: 0; /* Remove all margins */
            padding: 0; /* Remove any potential padding */
            padding-left: 20px;
            font-weight: 600;
            color: red;
        }

        .valid {
            color: green !important;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <h2><span>Change</span> <span>password.</span></h2>

        <form action="../server/newpass.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">

            <label>New Password</label>
            <div style="position: relative; width: 100%;">
                <input type="password" placeholder="Enter Password" name="password" id="password" required
                    style="padding-right: 40px;">
                <img id="togglePassword" src="../img/show.png" alt="Show Password"
                    style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; width: 20px; height: 20px;">
            </div>

            <label>Confirm Password</label>
            <div style="position: relative; width: 100%;">
                <input type="password" placeholder="Confirm Password" name="confirmPassword" id="confirmPassword" required
                    style="padding-right: 40px;">
                <img id="toggleConfirmPassword" src="../img/show.png" alt="Show Password"
                    style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; width: 20px; height: 20px;">
            </div>

            <!-- Display General Error Message if Set -->
            <?php if ($error_message): ?>
                <div class="error-message-container">
                    <p style="color: red; font-size: .7em;"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Display Password-Specific Error Message if Set -->
            <?php if ($password_error_message): ?>
                <div class="error-message-container">
                    <p style="color: red; font-size: .7em;"><?php echo nl2br(htmlspecialchars($password_error_message)); ?></p>
                </div>
            <?php endif; ?>

            <h2 class="password-rules">Password must contain:</h2>
            <p class="password-rules2" id="uppercase">
                • at least 1 uppercase letter<br>
            </p>
            <p class="password-rules2" id="specialChar">
                • at least 1 special character<br>
            </p>
            <p class="password-rules2" id="number">
                • at least 1 number<br>
            </p>
            <p class="password-rules2" id="length">
                • at least 8 characters or more<br>
            </p>
            <p class="password-rules2" id="match">
                • passwords must match<br>
            </p>

            <button type="submit" id="changePasswordButton" disabled>Change Password</button>

        </form>
    </div>

    <div id="popupMessage" class="popup-message"></div>

    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const toggleConfirmPassword = document.querySelector("#toggleConfirmPassword");
        const passwordInput = document.querySelector("#password");
        const confirmPasswordInput = document.querySelector("#confirmPassword");
        const changePasswordButton = document.querySelector("#changePasswordButton");
        const rules = {
            uppercase: /[A-Z]/,
            specialChar: /[!@#$%^&*(),.?":{}|<>]/,
            number: /[0-9]/,
            length: /.{8,}/
        };

        togglePassword.addEventListener("click", () => {
            // Toggle the password field type between 'password' and 'text'
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);

            // Change the icon based on the type
            togglePassword.src = type === "password" ? "../img/show.png" : "../img/hide.png";
        });

        toggleConfirmPassword.addEventListener("click", () => {
            // Toggle the confirm password field type between 'password' and 'text'
            const type = confirmPasswordInput.getAttribute("type") === "password" ? "text" : "password";
            confirmPasswordInput.setAttribute("type", type);

            // Change the icon based on the type
            toggleConfirmPassword.src = type === "password" ? "../img/show.png" : "../img/hide.png";
        });

        const validatePassword = () => {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            let allRulesMet = true;

            Object.keys(rules).forEach(rule => {
                const ruleElement = document.getElementById(rule);
                if (rules[rule].test(password)) {
                    ruleElement.classList.add("valid");
                } else {
                    ruleElement.classList.remove("valid");
                    allRulesMet = false;
                }
            });

            const matchElement = document.getElementById("match");
            if (password === confirmPassword && password !== "") {
                matchElement.classList.add("valid");
            } else {
                matchElement.classList.remove("valid");
                allRulesMet = false;
            }

            changePasswordButton.disabled = !allRulesMet;
        };

        passwordInput.addEventListener("input", validatePassword);
        confirmPasswordInput.addEventListener("input", validatePassword);

        // Function to display the popup
        function showMessage(type, message) {
            const popup = document.getElementById('popupMessage');
            popup.className = 'popup-message ' + type;
            popup.innerHTML = message;
            popup.style.display = 'block';

            // Hide the popup after 3 seconds
            setTimeout(() => {
                popup.style.display = 'none';
            }, 3000);
        }

        // Check for server-side messages
        <?php if (isset($_SESSION['success'])): ?>
            showMessage('success', "<?php echo addslashes($_SESSION['success']); ?>");
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            showMessage('error', "<?php echo addslashes($_SESSION['error']); ?>");
            <?php unset($_SESSION['error']); ?>
        <?php elseif (isset($_SESSION['password_error'])): ?>
            // Remove any mention of the token from the error message
            showMessage('error', "<?php echo addslashes($password_error_message); ?>");
            <?php unset($_SESSION['password_error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
