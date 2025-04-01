<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-Alert Landing Page</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FCFAFB;;
        }

        /* .hero {
            background-image: url("../img/Stay informed, stay prepared.png");
            background-size: cover;
            background-position: center center;
            text-align: left;
            padding: 100px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            margin: 120px 45px 0 45px;
            border: none;
            border-radius: 10px;
        } */

        .hero {
            background-image: url("../img/Stay informed, stay prepared.png");
            background-size: cover;
            background-position: center center;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #2B3467;
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #2B3467;
            transform: scaleX(0);
            transform-origin: bottom right;
            transition: transform 0.3s;
        }

        .nav-link:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        .nav-link.active,
        .nav-link:hover {
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-[#FCFAFB;]">
    <header class="fixed top-0 left-0 w-full z-[9999] flex justify-between items-center px-[40px] py-[60px] h-[70px] bg-[#FCFAFB;]">
        <div class="flex items-center">
            <div class="logo ml-[-25px] md:ml-[-20]">
                <img src="/img/LOGO 2.png" alt="i-Alert Logo" class="h-[90px] w-auto">
            </div>
            <nav class="hidden md:flex space-x-6 ml-10 w-auto flex-nowrap">
                <a href="#hero" class="nav-link font-semibold relative px-3 py-2 w-[150px] text-center" style="color: #2B3467;">Home</a>
                <a href="#about" class="nav-link font-semibold relative px-3 py-2 w-[150px] text-center" style="color: #2B3467;">About Cainta</a>
                <a href="#barangays" class="nav-link font-semibold relative px-3 py-2 w-[150px] text-center" style="color: #2B3467;">Barangays</a>
                <a href="#reach-us" class="nav-link font-semibold relative px-3 py-2 w-[150px] text-center" style="color: #2B3467;  ">Contact Us</a>
            </nav>

        </div>
        <div class="right-nav flex space-[10px] mr-[-15px]">
            <button id="menu-btn" class="md:hidden text-gray-800 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>

    </header>

    <nav id="mobile-menu" class="fixed top-0 left-0 w-full bg-[#FCFAFB] hidden md:hidden z-50">
        <ul class="flex flex-col items-center space-y-4 py-4 mt-[110px] text-[.6em]">
            <li><a href="#hero" class="nav-link text-gray-800 font-semibold relative px-3 py-2">Home</a></li>
            <li><a href="#about" class="nav-link text-gray-800 font-semibold relative px-3 py-2">About Cainta</a></li>
            <li><a href="#barangays" class="nav-link text-gray-800 font-semibold relative px-3 py-2">Barangays</a></li>
            <li><a href="#reach-us" class="nav-link text-gray-800 font-semibold relative px-3 py-2">Contact Us</a></li>
        </ul>
    </nav>




    <section id="hero" class="hero flex flex-col justify-center items-start mx-6 md:mx-12 rounded-lg mt-[120px] py-[100px] md:ml-[73px]">
        <h1 id="hero-heading" class="text-[2em] md:text-[3.2em] mr-[500px] font-extrabold text-gray-900 mx-10 mb-2">
            Loading Title...
        </h1>

        <p id="hero-paragraph" class="text-[.8em] md:text-[1em]  mr-[50px] md:mr-[820px] text-gray-900 mx-10 mb-8" style="color: #1F1F29;">Your safety is our priority. Stay updated with the latest alerts and information.</p>
        
        <button class="bg-gray-800 text-white rounded-lg ml-10 mb-12 text-[.8em] md:text-[1em] py-[10px] px-[20px] md:py-[10px] md:px-[50px]" 
                style="background-color: #2B3467; transition: background-color 0.3s; font-family: 'Poppins', sans-serif; font-weight: 600; border-radius: 10px;"
                onmouseover="this.style.backgroundColor='#1F2947'" 
                onmouseout="this.style.backgroundColor='#2B3467'"
                onclick="window.location.href='/mobile/MV-maps';">
            Get Started
        </button>
            
    </section>



    <section id="about" class="cainta text-center py-10">
        <h2 class="md:text-[2em] text-[1.2em] font-bold mb-4 mx-[50px]">The MDRRMO Cainta Mission and Vision</h2>
        <p class="md:text-[1em] text-[.6em] mx-[40px] mb-8">"Together We Thrive: Your Guide to Community Safety and Connection"</p>

        <div class="mv-grid flex justify-center space-x-6 mx-[20px] md:mx-[100px]">
            <div class="mission-card bg-[#1F1F2908] rounded-lg p-8 flex flex-col items-center hover:bg-[#1F1F2914] transform hover:scale-105 transition-transform flex-1 pb-[50px] pt-[50px]">
                <h3 id="mission-title" class="md:text-[1.5em] text-[.9em] font-bold mb-4">Mission</h3>
                <p id="mission-description" class="text-[#1F1F29]-700 md:text-[1em] text-[.7em]">Loading mission...</p>
            </div>
            <div class="vision-card bg-[#1F1F2908] rounded-lg p-8 flex flex-col items-center hover:bg-[#1F1F2914] transform hover:scale-105 transition-transform flex-1 pb-[50px] pt-[50px]">
                <h3 id="vision-title" class="md:text-[1.5em] text-[.9em] font-bold mb-4">Vision</h3>
                <p id="vision-description" class="text-[#1F1F29]-700 md:text-[1em] text-[.7em]">Loading vision...</p>
            </div>
        </div>

    </section>


    <section id="barangays" class="barangays text-center py-10">
        <h2 class="md:text-[2em] text-[1.2em] font-bold mb-4 mx-[50px]">Connecting with Your Barangay</h2>
        <p class="md:text-[1em] text-[.6em] mx-[40px] mb-8">"Get in Touch with Your Barangay: Contact Information for Every Community"</p>

        <!-- Container where the cards will be appended -->
        <div id="barangay-container" class="flex flex-wrap justify-center gap-6 w-full max-w-screen-xl mx-auto">
            <!-- Dynamic cards will be inserted here -->
        </div>
    </section>


    <section id="reach-us" class="reach-us text-center py-10 mb-[250px]">
        <h2 class="md:text-[2em] text-[1.2em] font-bold mb-4 mx-[50px]">Connecting to all first responders</h2>
        <p class="md:text-[1em] text-[.6em] mx-[40px] mb-8">"For Quick Assistance, Reach Out to the Following Emergency Numbers"</p>
        <div class="reach-info flex justify-center gap-6 mx-[20px] md:mx-[100px]">
            <!-- Contact cards will be dynamically inserted here by JavaScript -->
        </div>
    </section>


    <footer class="footer fixed bottom-0 w-full md:h-[50px] h-[30px] bg-[#f8f9fa] flex justify-between items-center py-2.5 px-5 shadow-[0_-2px_5px_rgba(0,0,0,0.1)]">
        <div class="footer-container flex w-full justify-between items-center">
            <div class="footer-left text-left">
                <p class="footer-text text-[#1F1F29] md:text-[.7em] text-[.3em] font-bold">Follow us on our social media account</p>
            </div>
            <div class="footer-center">
                <a href="https://www.cainta.gov.ph/" target="_blank" class="footer-link text-[#1F1F29] md:text-[.7em] text-[.3em] font-bold flex items-center gap-1">
                    https://www.cainta.gov.ph/
                </a>
            </div>
            <div class="footer-right text-right">
                <a href="https://www.facebook.com/onecainta.onecainta" target="_blank" class="footer-link md:text-[.7em] text-[.3em] font-bold flex items-center gap-1">
                    https://www.facebook.com/onecainta.onecainta
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const navLinks = document.querySelectorAll('.nav-link');
            const sections = document.querySelectorAll('section');
            const menuBtn = document.getElementById('menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');

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
                            top: targetSection.offsetTop - 70,
                            behavior: 'smooth'
                        });
                    } else {
                        window.scrollTo({
                            top: targetSection.offsetTop - 70,
                            behavior: 'smooth'
                        });
                    }

                    mobileMenu.classList.add('hidden');
                });
            });

            menuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            setActiveLinkOnScroll();
        });


        fetch('/server/fetch_homepage_herolanding_section.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                } else {
                    document.getElementById('hero-heading').innerText = data.heading;
                    document.getElementById('hero-paragraph').innerText = data.paragraph;
                    document.getElementById('hero-button').innerText = data.button_text;
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
                 
            
        
        fetch('/server/fetch_homepage_mvlanding_section.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('mission-title').textContent = data.data.mission_title;
                    document.getElementById('mission-description').textContent = data.data.mission_description;
                    document.getElementById('vision-title').textContent = data.data.vision_title;
                    document.getElementById('vision-description').textContent = data.data.vision_description;
                } else {
                    console.error(data.message);
                    document.getElementById('mission-description').textContent = 'Mission data not available.';
                    document.getElementById('vision-description').textContent = 'Vision data not available.';
                }
            })
            .catch(error => {
                console.error('Error fetching mission and vision:', error);
                document.getElementById('mission-description').textContent = 'Error loading mission.';
                document.getElementById('vision-description').textContent = 'Error loading vision.';
            });

        fetch('/server/fetch_homepage_brgcontact_section.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Select the correct container
                    const container = document.getElementById('barangay-container');
                    container.innerHTML = ''; // Clear any existing content

                    data.data.forEach((contact, index) => {
                        const card = document.createElement('div');
                        
                        // Apply different background color for odd and even index
                        const cardBackground = (index % 2 === 0) ? 'bg-[#1F1F2908]' : 'bg-[#1F1F2914]'; // Light color for even, dark for odd

                        card.classList.add(
                            'barangay-card', cardBackground, 'rounded-lg', 'p-8', 
                            'transform', 'hover:scale-105', 'transition-transform', 
                            'w-[280px]', 'flex', 'flex-col', 'items-center'
                        );

                        // Inner HTML for each card
                        card.innerHTML = `
                            <img src="${contact.logo || '../img/default-placeholder.png'}" 
                                alt="Logo of ${contact.barangay_name}" 
                                class="barangay-logo md:w-[90px] md:h-[90px] w-[70px] h-[70px] rounded-full mx-auto mb-7 mt-6">
                            
                            <h3 class="text-[.9em] md:text-[1em] font-[700] mb-7 text-center">${contact.barangay_name}</h3>
                            
                            <p class="text-[.6em] md:text-[.7em] text-center"><strong>Punong Barangay:</strong></p>
                            <p class="text-[.6em] md:text-[.7em] text-center mb-[10px]">${contact.punong_barangay}</p>

                            <p class="text-[.6em] md:text-[.7em] text-center"><strong>Contact No.:</strong></p>
                            <p class="text-[.6em] md:text-[.7em] text-center mb-[10px]">${contact.contact_number}</p>

                            <p class="text-[.6em] md:text-[.7em] text-center"><strong>Email:</strong></p>
                            <p class="text-[.6em] md:text-[.7em] text-center mb-[10px]">${contact.email}</p>

                            <p class="text-[.6em] md:text-[.7em] text-center"><strong>Address:</strong></p>
                            <p class="text-[.6em] md:text-[.7em] text-center mb-6">${contact.address}</p>
                        `;


                        // Append the card to the container
                        container.appendChild(card);
                    });
                } else {
                    console.error(data.message);
                    document.getElementById('barangay-container').innerHTML = '<p>Barangay contact data not available.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching barangay contact data:', error);
                document.getElementById('barangay-container').innerHTML = '<p>Error loading barangay contact data.</p>';
            });





        fetch('/server/fetch_homepage_econ_section.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.querySelector('.reach-info');
                    container.innerHTML = '';

                    data.data.forEach(contact => {
                        if (contact.contact_number) {
                            const card = document.createElement('div');
                            card.classList.add('reach-card', 'bg-[#1F1F2908]', 'rounded-lg', 'p-8', 'hover:bg-[#1F1F2914]', 'transform', 'hover:scale-105', 'transition-transform', 'w-[300px]', 'max-w-[500px]');
                            card.innerHTML = `
                                <h3 class="text-2xl font-semibold mb-2">${contact.contact_number}</h3>
                                <p class="text-gray-700">${contact.description}</p>
                            `;
                            container.appendChild(card);
                        }

                        if (contact.contact_number_2) {
                            const card2 = document.createElement('div');
                            card2.classList.add('reach-card', 'bg-[#1F1F2908]', 'rounded-lg', 'p-8', 'hover:bg-[#1F1F2914]', 'transform', 'hover:scale-105', 'transition-transform', 'w-[300px]', 'max-w-[500px]');
                            card2.innerHTML = `
                                <h3 class="text-2xl font-semibold mb-2">${contact.contact_number_2}</h3>
                                <p class="text-gray-700">${contact.description_2}</p>
                            `;
                            container.appendChild(card2);
                        }
                    });
                } else {
                    console.error(data.message);
                    document.querySelector('.reach-info').innerHTML = '<p>Emergency contact data not available.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching emergency contact data:', error);
                document.querySelector('.reach-info').innerHTML = '<p>Error loading emergency contact data.</p>';
            });
    </script>
</body>
</html>
