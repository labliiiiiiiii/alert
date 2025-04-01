<?php
session_start();
include('../component/popupmsg.php'); // Include the function file
displayPopupMessage(); // Call the function to display any messages
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

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

        .forgot-password-container {
            background: rgba(255, 255, 255, 0.6); /* White with 60% opacity */
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            width: 90%;
            max-width: 360px;
        }

        .forgot-password-container h2 {
            font-size: 2em;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 0px;
            padding: 30px 0px 0px 0px;
            text-align: left;
        }

        .forgot-password-container h2 span:first-child {
            color: #1F1F29; /* Color for "Forgot" */
        }

        .forgot-password-container h2 span:last-child {
            color: #2B3467; /* Color for "password?" */
        }

        .forgot-password-container p {
            font-size: 0.90em;
            font-weight: 500;
            color: #1F1F29;
            margin-bottom: 30px;
            padding: 0px 10px 0px 0px;
            text-align: left;
        }

        .forgot-password-container label {
            font-size: 0.90em; /* Updated font size */
            font-weight: 600;
            text-align: left; /* Align label text to the left */
            display: block;
            margin-top: 10px;
            color: #1F1F29;
        }

        .forgot-password-container input {
            width: 100%;
            padding: 10px;
            margin: 0px 10px 5px 0px;
            border: 2px solid #bdc3c7;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.90em;
            box-sizing: border-box;
        }

        .forgot-password-container input:hover {
            background-color: #e9ecef;
        }

        .forgot-password-container input:focus {
            outline: none; /* Remove the default browser outline */
            border: 2px solid #2B3467; /* Highlighted border */
            background-color: #f9f9f9; /* Slightly different background for focus */
        }

        .forgot-password-container button {
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

        .forgot-password-container button:hover {
            background-color: #1F2947;
        }

        .forgot-password-container a {
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

        .forgot-password-container a:hover {
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
            font-weight: 600;
            color: #2B3467; /* Link color */
            text-decoration: none; /* Remove underline */
            margin-left: 5px; /* Add spacing between text and link */
        }

        .request-account a:hover {
            text-decoration: underline; /* Add underline on hover */
        }

        /* Media Queries */
        @media (max-width: 1366px) {
            .forgot-password-container {
                width: 150%;
                padding: 20px 30px;
            }

            .forgot-password-container h2 {
                font-size: 2em;
            }

            .forgot-password-container p {
                font-size: 0.9em;
            }

            .forgot-password-container input {
                font-size: 0.9em;
            }

            .forgot-password-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }

        @media (max-width: 768px) {
            .forgot-password-container {
                width: 95%;
                padding: 20px 30px;
            }

            .forgot-password-container h2 {
                font-size: 1.5em;
            }

            .forgot-password-container p {
                font-size: 0.9em;
            }

            .forgot-password-container input {
                font-size: 0.9em;
            }

            .forgot-password-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }

        @media (max-width: 480px) {
            .forgot-password-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .forgot-password-container h2 {
                font-size: 1.2em;
            }

            .forgot-password-container p {
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
            .forgot-password-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .forgot-password-container h2 {
                font-size: 1em;
            }

            .forgot-password-container p {
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

    <div class="forgot-password-container">
        <h2><span>Forgot</span> <span>password?</span></h2>
        <p>Please provide your email address to receive instructions.</p>

        <form action="../server/sendEmailFP.php" method="POST">
            <label>Email Address</label>
            <input type="email" placeholder="Enter your email address..." name="email" required>
            <button type="submit">Send Instructions</button>
        </form>

        <div class="request-account">
            <span>Remembered your password?</span>
            <a href="../pages/adminloginpage.php">Login now!</a>
        </div>
    </div>

</body>
</html>
