<?php
    session_start();
    $error = $_SESSION['error'] ?? ''; // Retrieve error message if set
    unset($_SESSION['error']); // Clear the error after displaying
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
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
            max-width: 350px;
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
            color: #1F1F29; /* Color for "Welcome" */
        }

        .login-container h2 span:last-child {
            color: #2B3467; /* Color for "back." */
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
            margin: 0px 10px 20px 0px;
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
            margin-bottom: 30px;
        }

        .login-container button:hover {
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


        /* Media Queries */
        @media (max-width: 1366px) {
            .login-container {
                width: 150%;
                padding: 20px 30px;
            }

            .login-container h2 {
                font-size: 2em;
            }

            .login-container p {
                font-size: 0.9em;
            }

            .login-container input {
                font-size: 0.9em;
            }

            .login-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }


        @media (max-width: 768px) {
            .login-container {
                width: 95%;
                padding: 20px 30px;
            }

            .login-container h2 {
                font-size: 1.5em;
            }

            .login-container p {
                font-size: 0.9em;
            }

            .login-container input {
                font-size: 0.9em;
            }

            .login-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .login-container h2 {
                font-size: 1.2em;
            }

            .login-container p {
                font-size: 0.8em;
            }

            .request-account {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .request-account a {
                margin-left: 0;
                margin-top: 5px;
            }
        }

        @media (max-width: 200px) {
            .login-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .login-container h2 {
                font-size: 1em;
            }

            .login-container p {
                font-size: 0.6em;
            }

            .request-account {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .request-account a {
                margin-left: 0;
                margin-top: 5px;
            }
        }


    </style>
</head>
<body>


    <div class="login-container">
        <h2><span>Create</span> <span>username.</span></h2>
        <p>Please create a unique username.</p>

        <form action="../server/save_username.php" method="POST">
            <label>Create Username</label>
            <input type="text" name="username" required>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="error-message-container">
                    <p style="color: red; font-size: .7em; "><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <button type="submit">Done</button>
            


            
        </form>

    </div>

</body>
</html>
