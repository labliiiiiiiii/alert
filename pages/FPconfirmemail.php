<?php
// Ensure the email is passed as a GET parameter
if (isset($_GET['email'])) {
    $email = htmlspecialchars($_GET['email']);
} else {
    // Redirect or handle error if email is not set
    header("Location: error_page.php"); // Create an error page if needed
    exit();
}
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

        .reset-container {
            background: rgba(255, 255, 255, 0.6);
            padding: 20px 30px;
            border-radius: 10px;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .reset-container h2 {
            font-size: 2em;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 0px;
            padding: 30px 0px 0px 0px;
            text-align: left;
        }

        .reset-container h2 span:first-child {
            color: #1F1F29;
        }

        .reset-container h2 span:last-child {
            color: #2B3467;
        }

        .reset-container p {
            font-size: 0.90em;
            font-weight: 500;
            color: #1F1F29;
            margin-bottom: 30px;
            padding: 0px 10px 0px 0px;
            text-align: left;
        }

        .reset-container label {
            font-size: 0.90em;
            font-weight: 600;
            text-align: left;
            display: block;
            margin-top: 10px;
            color: #1F1F29;
        }

        .reset-container input {
            width: 100%;
            padding: 10px;
            margin: 0px 10px 5px 0px;
            border: 2px solid #bdc3c7;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.90em;
            box-sizing: border-box;
        }

        .reset-container input:hover {
            background-color: #e9ecef;
        }

        .reset-container input:focus {
            outline: none;
            border: 2px solid #2B3467;
            background-color: #f9f9f9;
        }

        .reset-container .email {
            font-weight: 700;
        }

        .reset-container button {
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
        }

        .reset-container button:hover {
            background-color: #1F2947;
        }

        .reset-container a {
            display: inline; /* Ensure links are inline */
            color: #2B3467; /* New color for links */
            text-decoration: none;
            font-weight: 600;
        }

        .reset-container a:hover {
            text-decoration: underline;
            color: #1F1F29;
        }

        .support-text {
            display: inline;
            font-size: 0.90em;
            font-weight: 500;
            color: #1F1F29;
        }

        .support-link {
            color: #FF5733; /* Change to your desired color */
        }

        .support-link:hover {
            color: #C70039; /* Darker shade for hover effect */
        }

        .resend-link {
            color: #FF5733; /* Change to your desired color */
        }

        .resend-link:hover {
            color: #C70039; /* Darker shade for hover effect */
        }

        .request-account {
            display: flex;
            justify-content: center;
            align-items: baseline;
            font-size: 0.70em;
            color: #1F1F29;
            margin-top: 20px;
            font-weight: 500;
        }

        .request-account span {
            font-size: inherit;
            color: inherit;
        }

        .request-account a {
            font-size: inherit;
            font-weight: 600;
            color: #2B3467;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .request-account a:hover {
            text-decoration: underline;
        }

        .reset-container .back-link {
            margin-top: 100px;
            text-align: center;
        }
        .reset-container .back-link a:hover {
            text-decoration: none;
        }

        /* Media Queries */
        @media (max-width: 1366px) {
            .reset-container {
                width: 150%;
                padding: 20px 30px;
            }

            .reset-container h2 {
                font-size: 2em;
            }

            .reset-container p {
                font-size: 0.9em;
            }

            .reset-container input {
                font-size: 0.9em;
            }

            .reset-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }

        @media (max-width: 768px) {
            .reset-container {
                width: 95%;
                padding: 20px 30px;
            }

            .reset-container h2 {
                font-size: 1.5em;
            }

            .reset-container p {
                font-size: 0.9em;
            }

            .reset-container input {
                font-size: 0.9em;
            }

            .reset-container button {
                font-size: 0.9em;
            }

            .back-button img {
                width: 16px;
            }
        }

        @media (max-width: 480px) {
            .reset-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .reset-container h2 {
                font-size: 1.2em;
            }

            .reset-container p {
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
            .reset-container {
                width: 80%;
                padding: 20px 30px;
                margin: 30px;
            }

            .reset-container h2 {
                font-size: 1em;
            }

            .reset-container p {
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

    <div class="reset-container">
        <h2><span>Check your</span> <span>email.</span></h2>
        <p>We've sent password reset instructions to:</p>
        <p class="email"><?php echo $email; ?></p>
        <p class="spam-note">If it doesn't arrive soon, check your spam folder or <a href="../server/sendEmailFP.php?email=<?php echo urlencode($email); ?>" class="resend-link">send the email again</a>

        <p class="support"><span class="support-text">Need help?</span> <a href="mailto:your-email@example.com" class="support-link">Contact support</a>.</p>
        <p class="back-link"><a href="../pages/adminloginpage">Back to sign-in</a></p>
    </div>

</body>
</html>
