<?php
include_once '../pages/auth_check.php';

// Allow only admin users
$allowedUserTypes = ['admin'];
check_auth($allowedUserTypes);

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../server/connect.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Landing Page</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #FCFAFB;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            height: 70px;
            background-color: #FCFAFB;
        }

        header .logo img {
            height: 90px;
            width: auto;
        }

        header .logo-and-nav {
            display: flex;
            align-items: center;
        }

        header .left-nav ul {
            display: flex;
            list-style: none;
            margin: 30px;
            padding: 0;
            gap: 20px;
        }

        header .left-nav ul li a {
            text-decoration: none;
            color: #2B3467;
            font-weight: 600;
            font-size: 1em;
            padding: 10px 10px;
            position: relative;
            display: inline-block;
            width: 120px;
            text-align: center;
        }

        .left-nav ul li a.active {
            color: #1F2947;
            font-weight: bold;
        }

        header .left-nav ul li a.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #2B3467;
            transform: scaleX(1);
            transform-origin: bottom left;
            transition: none;
        }

        header .left-nav ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #2B3467;
            transform: scaleX(0);
            transform-origin: bottom right;
        }

        header .left-nav ul li a:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        .right-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-right: 100px;
        }

        .right-nav .btn {
            padding: 10px 20px;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            cursor: pointer;
            border: 3px solid #2B3467;
            box-sizing: border-box;
            width: auto;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .right-nav .btn.sign-up {
            width: 150px;
            color: #2B3467;
            text-align: center;
        }

        .right-nav .btn.sign-up:hover {
            background-color: #e9ecef;
        }

        .right-nav .btn.login {
            width: 150px;
            background-color: #2B3467;
            color: #fff;
            text-align: center;
        }

        .right-nav .btn.login:hover {
            background-color: #1F2947;
        }

        .hero {
            background-image: url("../img/Stay informed, stay prepared.png");
            background-size: cover;
            background-position: center center;
            text-align: left;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            margin: 120px 45px 0 45px;
            border: none;
            border-radius: 10px;
            margin-bottom: 40px;
        }

        .hero h1 {
            font-size: 3.2em;
            margin-left: 63px;
            margin-bottom: 5px;
            padding-right: 400px;
            color: #1F1F29;
            font-weight: 900;
        }

        .hero p {
            font-size: 1em;
            margin-left: 63px;
            margin-bottom: 0px;
            padding-right: 820px;
            color: #1F1F29;
        }

        .hero button {
            padding: 10px 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 1em;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            background-color: #2B3467;
            color: #fff;
            width: 190px;
            height: 45px;
            cursor: pointer;
            margin-left: 63px;
            margin-top: 30px;
            margin-bottom: 50px;
        }

        .hero button:hover {
            background-color: #1F2947;
        }

        

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
            font-family: Poppins, sans-serif;
            height: 30px;
        }

        .footer-container {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left,
        .footer-center,
        .footer-right {
            flex: none;
            text-align: center;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
            margin-right: 40px;
        }

        .footer-link {
            font-size: 0.7em;
            color: #1F1F29;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        footer a, .footer-text {
            font-size: 0.7em !important;
            font-weight: 700;
            color: #1F1F29;
            text-decoration: none;
            padding: 5px;
        }

        html {
            scroll-behavior: smooth;
        }

        #hero {
            scroll-padding-top: 190px;
        }

        #about {
            scroll-padding-top: 50px;
        }

        #barangays {
            scroll-padding-top: 50px;
        }

    </style>

</head>
<body>
    <header>
        <div class="logo-and-nav">
            <div class="logo">
                <img src="../img/LOGO 2.png" alt="i-Alert Logo">
            </div>
            <nav class="left-nav">
                <ul>
                   
                </ul>
            </nav>
        </div>
        <nav class="right-nav">
            <a href="signuppage" class="btn sign-up">Sign Up</a>
            <a href="caintamappage" class="btn login">Get Started</a>
        </nav>
    </header>

    <section id="hero" class="hero">
        <h1>Welcome to the ADMIN Panel</h1>
        <p>Manage critical resources, monitor safety protocols, and stay up-to-date with the latest updates for a safer community.</p>
        <button>Get Started</button>
    </section>




    <footer class="footer">
        <div class="footer-container">
            <div class="footer-left">
                <p class="footer-text">Follow us on our social media account</p>
            </div>
            <div class="footer-center">
                <a href="https://www.cainta.gov.ph/" target="_blank" class="footer-link">
                    https://www.cainta.gov.ph/
                </a>
            </div>
            <div class="footer-right">
                <a href="https://www.facebook.com/onecainta.onecainta" target="_blank" class="footer-link">
                    https://www.facebook.com/onecainta.onecainta
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const navLinks = document.querySelectorAll('.nav-link');
            const sections = document.querySelectorAll('section');

            function setActiveLinkOnScroll() {
                let currentSection = '';

                sections.forEach(section => {
                    const sectionTop = section.offsetTop - 300;
                    const sectionBottom = sectionTop + section.offsetHeight;
                    const scrollPosition = window.scrollY;

                    if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                        currentSection = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    if (link.getAttribute('href').substring(1) === currentSection) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
            }

            window.addEventListener('scroll', setActiveLinkOnScroll);

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);

                    if (targetId === 'hero') {
                        window.scrollTo({
                            top: targetSection.offsetTop - 190,
                            behavior: 'smooth'
                        });
                    } else if (targetId === 'about') {
                        window.scrollTo({
                            top: targetSection.offsetTop - 50,
                            behavior: 'smooth'
                        });
                    } else {
                        window.scrollTo({
                            top: targetSection.offsetTop - 70,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            setActiveLinkOnScroll();
        });
    </script>
</body>
</html>
