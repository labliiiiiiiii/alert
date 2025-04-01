<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>

    <style type="text/css">
        /* @font-face for custom font */
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            src: local('Poppins'), url('https://fonts.gstatic.com/s/poppins/v15/pxiEyp8kv8JHgFVsQyXTU5g.woff2') format('woff2');
        }

        body {
            font-family: 'Poppins', Helvetica, sans-serif; /* Poppins with fallbacks */
            background-color: #FCFAFB;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            padding: 50px;
            border-radius: 8px;
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            height: auto;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo img {
            margin-left: -30px;
            width: 150px;
            margin-right: 20px;
        }

        .content {
            background-color: #FCFAFB;
            border: 2px solid #dddddd;
            padding: 40px;
            border-radius: 8px;
        }

        .message {
            margin-bottom: 20px;  
            margin-left: 10px;
            margin-right: 10px;
        }

        .message p:first-child {
            margin-top: 10px;
        }

        .button {
            text-decoration: none;
            text-align: center;
            width: 200px;
            font-family: 'Poppins', Helvetica, sans-serif; /* Poppins with fallbacks */
            background-color: #2B3467;
            color: #fff !important;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            display: block;
            margin: 0 auto; /* Center the button */
        }

        .button:hover {
            background-color: #1F2947;
        }

        .footer {
            font-family: 'Poppins', Helvetica, sans-serif; /* Poppins with fallbacks */
            color: #888888;
            
            margin-top: 20px;
            margin-left: 10px;
            margin-right: 10px;
        }

        .footer a {
            color: #2B3467;
            text-decoration: none;
        }

        .footer a:hover {
            color: #1F2947;
            text-decoration: underline;
        }


        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="../img/LOGO 2.png" alt="Logo">
            </div>
        </div>
        <div class="content">
            <div class="message">
                <p style="font-size: 2em; font-weight: 600; margin-top: 50px; margin-bottom: 15px;">Hello {{username}},</p>
                <p style="font-size: 1.3em; font-weight: 500; margin-top: 0px; margin-bottom: 30px;">A request has been received to change the password for your account.</p>
            </div>
            <a href="{{reset_link}}" class="button">Reset Password</a>


            <div class="footer">
                <p style="font-size: 1.3em !important; font-weight: 500; margin-bottom: 50px; margin-top: 80px;">If you did not initiate this request, please contact us immediately at <a href="mailto:support@example.com">support@example.com</a>.</p>
                <p style="font-size: 1.3em !important; font-weight: 500; margin-bottom: 20px;">Thank you,<br>The CTRL Freaks</p>
            </div>
        </div>
        <div class="footer" style="font-size: .8em; text-align: center; margin-top: 30px; margin-bottom: 20px;">
            <p>&copy; CTRL FREAKS. URS-Binangonan | College of Computer Studies | 2024-2025</p>
            <p>
                <a href="https://sendgrid.com/blog">Website</a> |
                <a href="https://github.com/sendgrid">Facebook</a>
            
            </p>
        </div>
        
    </div>
</body>
</html>
