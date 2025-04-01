<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Same CSS styling you provided */
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

        .login-container {
            background: rgba(255, 255, 255, 0.6); /* White with 60% opacity */
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            width: 90%;
            max-width: 370px;
        }

        .login-container h2 {
            font-size: 2em;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 0px;
            padding: 30px 0px 0px 0px;
            text-align: left;
        }

        .login-container h2 span:first-child {
            color: #1F1F29; /* Color for "Sign up" */
        }

        .login-container h2 span:last-child {
            color: #2B3467; /* Color for "now." */
        }

        .login-container p {
            font-size: 0.90em;
            font-weight: 500;
            color: #1F1F29;
            margin-bottom: 30px;
            padding: 0px 10px 0px 0px;
            text-align: left;
        }

        .login-container label {
            font-size: 0.90em; /* Updated font size */
            font-weight: 600;
            text-align: left; /* Align label text to the left */
            display: block;
            margin-top: 10px;
            color: #1F1F29;
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 0px 10px 5px 0px;
            border: 2px solid #bdc3c7;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.90em;
            box-sizing: border-box;
        }

        .login-container input:hover {
            background-color: #e9ecef;
        }

        .login-container input:focus {
            outline: none; /* Remove the default browser outline */
            border: 2px solid #2B3467; /* Highlighted border */
            background-color: #f9f9f9; /* Slightly different background for focus */
        }

        .login-container button {
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
            margin-bottom: 20px;
        }

        .login-container button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .login-container button:hover:not(:disabled) {
            background-color: #1F2947;
        }

        .login-container a {
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

        .login-container a:hover {
            text-decoration: underline;
            color: #1F1F29;
        }

        .request-account {
            display: flex; /* Flexbox for alignment */
            justify-content: center; /* Center horizontally */
            align-items: baseline; /* Align vertically */
            font-size: 0.70em; /* Set font size for both text and link */
            color: #1F1F29; /* Set the color for the regular text */
            margin-top: 20px; /* Add some spacing above */
            font-weight: 500; /* Slightly bold for the regular text */
        }

        .request-account span {
            font-size: inherit; /* Same size as parent */
            color: inherit; /* Regular text color */
        }

        .request-account a {
            font-size: inherit; /* Same size as parent */
            font-weight: 600;;
            color: #2B3467; /* Link color */
            text-decoration: none; /* Remove underline */
            font-weight: 600; /* Bold link */
            margin-left: 5px; /* Add spacing between text and link */
        }

        .request-account a:hover {
            text-decoration: underline; /* Add underline on hover */
        }

        .return-home {
            position: absolute; /* Position the container absolutely */
            top: 20px; /* Add some spacing from the top */
            left: 20px; /* Add some spacing from the left */
            z-index: 10; /* Ensure it appears above other elements */
        }

        .back-button {
            position: relative;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            color: #2B3467;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-button:hover {
            color: #1F1F29;
        }

        .back-button img {
            width: 20px;
            height: auto;
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
    <div id="popupMessage" class="popup-message"></div>

    <div class="login-container">
        <h2><span>Sign up</span> <span>now.</span></h2>
        <p>Please enter your account details.</p>

        <form action="../server/send_verification.php" method="POST">
            <label>Email</label>
            <input type="text" placeholder="Enter Email" name="email" required>

            <label>Password</label>
            <div style="position: relative; width: 100%;">
                <input type="password" placeholder="Enter Password" name="password" id="password" required
                    style="padding-right: 40px;">
                <img id="togglePassword" src="../img/show.png" alt="Show Password"
                    style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; width: 20px; height: 20px;">
            </div>

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

            <!-- Display All Messages (Errors or Success) -->
            <?php if (!empty($messages)): ?>
                <div class="message-container">
                    <?php foreach ($messages as $message): ?>
                        <p style="color: <?php echo ($message['type'] == 'error') ? 'red' : 'green'; ?>; font-size: .7em;">
                            <?php echo htmlspecialchars($message['text']); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button type="submit" id="nextButton" disabled>Next</button>
        </form>

    </div>

    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const passwordInput = document.querySelector("#password");
        const nextButton = document.querySelector("#nextButton");
        const rules = {
            uppercase: /[A-Z]/,
            specialChar: /[!@#$%^&*(),.?":{}|<>]/,
            number: /[0-9]/,
            length: /.{8,}/
        };

        togglePassword.addEventListener("click", () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            togglePassword.src = type === "password" ? "../img/show.png" : "../img/hide.png";
        });

        passwordInput.addEventListener("input", () => {
            const password = passwordInput.value;
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

            nextButton.disabled = !allRulesMet;
        });
    </script>
</body>
</html>
