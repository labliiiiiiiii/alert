    <?php
    // Start session and include necessary files
    include '../component/navbar.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include_once '../server/connect.php';

    // Include the appropriate sidebar based on userType
    if ($_SESSION['position'] === 'MDRRMO Cainta') {
        include '../component/adminsidebar.php'; // Admin sidebar
    } elseif ($_SESSION['position'] === 'BRGY Staff') {
        include '../component/brgysidebar.php'; // Barangay sidebar
    } else {
        // Handle unknown user types
        echo "Invalid user type!";
        exit();
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Severe Weather Update</title>

    <link rel="stylesheet" href="../style/showArchive_MODAL.css?v=<?php echo time(); ?>">

    <!-- Add this to the <head> section -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Add this before the closing </body> tag -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            background-color: #FCFAFB;
        }

        .container {
            width: calc(100% - 300px); /* Adjusts width */
            margin-left: 260px; /* Space on the left */
            margin-bottom: 20px; /* Leaves 10px margin at the bottom */
            padding: 0px; /* No internal padding */
            border-radius: 10px; /* Rounded corners */

            min-height: calc(100vh - 200px); /* Minimum height to ensure it doesn't shrink */
            height: auto; /* Automatically adjusts height based on content */
        }

        /* Adjust the post container for better alignment */
        .post-container {
            padding: 20px;
            margin-top: 30px;
            
            border-radius: 10px;
            border: 2px solid rgba(31, 31, 41, 0.15); /* Border */
        }
        .header {
            display: flex;
            flex-direction: column;
            font-size: 1em;
            color: #4F4F4F;
            position: relative;
            overflow: hidden !important;  /* Double-check this for issues */
        }

        /* Flexbox layout for View button and ellipsis - without absolute positioning */
        .mamamo-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px; /* Space between the ellipsis and the view button */
            margin-top: -50px; /* Adjusts vertical spacing if needed */
        }

        .view-button {
            font-family: 'Poppins', sans-serif;
            padding: 10px 20px;
            background-color: #2B3467;
            color: white;
            border: 2px solid  #2B3467;
            border: none;
            cursor: pointer;
            font-size: 0.8em;
            font-weight: 600;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-right: 0px;
            cursor: pointer;
            margin-top: -20px;
        }

        .view-button:hover {
            background-color: #1D223F;
            color: white;
        }

        /* Container for author and logo side by side */
        .author-logo-container {
            display: flex;
            align-items: flex-start;  
            gap: 10px;
            flex-direction: row;
            margin-bottom: 10px;
        }


        /* Author styling */
        .author {
            display: flex;
            align-items: center; /* Ensure the author text and logo are beside each other */
            font-size: 0.90em;
            color: #1F1F29;
            font-weight: 600;
            margin-bottom: 0; /* Space between the author and post text */
        }

        /* Logo styling */
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo {
            width: 70px; /* Adjust width of the logo */
            height: auto; /* Maintain aspect ratio */
            border-radius: 5px; /* Optional: Add rounded corners to the logo */
        }

        .post-content {
            display: flex;
            flex-direction: column;
            margin-left: 5px;
            margin-top: 10px;
            gap: -10px;
            height: auto;
            overflow: hidden !important; /* Prevent overflow */
            padding-right: 0px; /* Remove right padding to avoid space */
        }


        .post-text p {
            padding-right: 100px;
            font-weight: 600;
            font-size: 0.8em;
            color: rgba(31, 31, 41, 0.7);
            margin: 0;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important; /* Limit text to 2 lines */
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important; /* Optional: adds ellipsis for overflowed text */
            line-height: 1.5em !important; /* Set the line height */
            max-height: 4.5em !important; /* Ensure 2 lines' height (2 * 1.5em) */
            word-wrap: break-word; /* Prevent long words from spilling out */
            hyphens: auto; /* Add hyphenation for better line breaks */
        }

        .author,
        .post-text {
            margin-bottom: -5px; /* Adjust margins to make them closer */
        }

        .post-text p {
            word-wrap: break-word; /* Prevents unbreakable content from spilling out */
            hyphens: auto; /* Adds hyphenation for better line breaks */
        }


        .created-post {
            font-style: italic;
            font-weight: 400; /* Adjust font weight for "created a post" text */
            color: #333;
        }

        .date {
            color: #888;
            font-size: 1em;
            color: #1F1F29;
            margin-bottom: 15px;
        }

        .contentArchive label {
            display: flex;
            align-items: center; /* Ensures both checkbox and text are aligned in the center */
        }

        .archive {
            font-size: .60em;
            color: rgba(31, 31, 41, 0.7);
            text-align: right;
            font-style: italic;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .sort-by-section {
            margin-left: 260px; /* Align with the announcement section */
            justify-content: flex-start; /* Align buttons to the left or keep them aligned based on your preference */
            margin-bottom: 20px; /* Space between sort-by and announcements */
            display: flex;
            align-items: center;
            gap: 10px; /* Space between label and dropdown */
            
        }

        .sort-by-dropdown {
            -webkit-appearance: none; /* Remove default dropdown arrow in WebKit-based browsers (Chrome, Safari) */
            -moz-appearance: none;    /* Remove in Firefox */
            appearance: none;         /* For modern browsers */

            
            padding: 12px 12px;
            border: 2px solid rgba(31, 31, 41, 0.15);
            border-radius: 5px;
            background-color: #FCFAFB;
            font-family: 'Poppins', sans-serif;
            font-size: 0.8em;
            font-weight: 500;
            color: #1F1F29B3;
            cursor: pointer;
            transition: border-color 0.3s ease;
            width: 200px;

            background-image: url('../img/icons/dropdownD.png'); /* Custom icon for the select dropdown */
            background-repeat: no-repeat;
            background-position: right 10px center; /* Position the icon on the right */
            background-size: 26px, 25px;

        }

        .sort-by-dropdown:hover {
            border-color: #2B3467;
            background-color: #1F1F290D;
            color: #1F1F29 !important;
        }

        .sort-by-dropdown:focus {
            outline: none;
            border-color: #2B3467;
        }

        #date-range-input {
            font-family: 'Poppins', sans-serif;
            padding: 12px 20px;
            border: 2px solid rgba(31, 31, 41, 0.15);
            border-radius: 5px;
            color: #1F1F29B3;
            background-color: #FCFAFB;
            cursor: pointer;
            font-size: 0.8em;
            font-weight: 600;
            width: 250px; /* Set a fixed width for the date range input */
            resize: none; /* Prevent resizing */
            text-align: center;
            background-image: url("../img/icons/calendar-icon.png"), url("../img/icons/dropdownD.png");
            background-repeat: no-repeat;
            background-position: 10px center, right 10px center;
            background-size: 20px, 27px;
            transition: background-color 0.3s ease;
            resize: none;
            box-sizing: border-box;
        }

        #date-range-input:hover {
            background-color: #1F1F290D;
            border-color: #2B3467;
            /* Combine the left and right icons into one background-image */
            background-image: url("../img/icons/calendar-iconS.png"), url("../img/icons/dropdownS.png");
            background-repeat: no-repeat;
            background-position: 10px center, right 10px center; /* Position the left icon and right icon */
            background-size: 20px, 27px; /* First value (left icon), second value (right icon) */
        }

        #date-range-input::placeholder {
            transition: background-color 0.3s ease;
            margin-right: 7px;
        }

        #date-range-input:hover::placeholder {
            color: #1F1F29 !important;
            
        }

        #date-range-input:focus {
            background-color: #FCFAFB; /* Set to your preferred color */
            border: 2px solid #2B3467 !important;
            color: #1F1F29 !important;
            outline: none;
        }

        /* Change background color of the calendar container */
        .flatpickr-calendar {
            font-family: 'Poppins', sans-serif;
            
            background-color: #FCFAFB; /* Set to your preferred color */
            border-radius: 10px; /* Optional: to round the corners */
            border: 2px solid #1F1F29;
            
        }

        /* Change the text color of the days */
        .flatpickr-day {
            color: #1F1F29; /* Change this to your preferred text color */
            font-size: .80em; /* Adjust the font size as needed */
        }

        /* Highlight selected date */
        .flatpickr-day.selected {
            background-color: #2B3467 !important; /* Your preferred background color */
            color: #FFFFFF !important; /* Your preferred text color */
            border: none;
            font-size: 1em; /* Adjust the font size as needed */
            font-weight: 600;
        }

        /* Change color for the hovered date */
        .flatpickr-day:hover {
            background-color: #1F1F2926; /* Change to your preferred hover color */
            font-size: 1em; /* Adjust the font size as needed */
            font-weight: 600;
        }

        .flatpickr-day.selected:hover {
            background-color: #1D223F !important; /* Darker shade for hover effect */
        }

        /* Remove blue background from range selection */
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #2c3354 !important; /* Change to your desired color */
            color: white !important;
            border: none !important; /* Remove the border */
            
        }

        

        /* Change the color of the current date */
        .flatpickr-day.today {
            color: #1F1F29; /* Change to your preferred text color */
            border: 2px solid #1F1F29;
        }

        /* Change the color of the month dropdown items */
        .flatpickr-monthDropdown-months {
            background-color: #FCFAFB; /* Set to your preferred color */
            color: #1F1F29; /* Set to your preferred text color */
        }

        /* Change the background color of the dropdown when hovering */
        .flatpickr-monthDropdown-months:hover {
            background-color: #FCFAFB; /* Adjust to your hover color */
            color: #1F1F29; /* Change text color when hovering */
            font-weight: 600;
        }


        /* Change the color of the calendar navigation year dropdown */
        .flatpickr-yearDropdown {
            background-color: #FCFAFB; /* Set to your preferred background */
            color: #1F1F29; /* Set to your preferred text color */
        }

        /* Change the color of the month and year text */
        .flatpickr-monthDropdown-months, .flatpickr-yearDropdown {
            color: #1F1F29;    
        }

        /* Change the color of the calendar navigation arrows */
        .flatpickr-prev-month, .flatpickr-next-month {
            color: #1F1F29; /* Change to your preferred color */
        }

        /* Change the font size of the weekdays */
        .flatpickr-weekdays {
            font-family: 'Poppins', sans-serif;
            font-size: .8em; /* Adjust the font size as needed */
        }

        .flatpickr-current-month {
            font-family: 'Poppins', sans-serif;
            font-size: 1.1em; /* Adjust the font size as needed */
        }

        #reset-btn {
            font-family: 'Poppins', sans-serif;
            padding: 13px 20px;
            background-color: #2B3467;
            color: white;
            border: 2px solid  #2B3467;
            border: none;
            cursor: pointer;
            font-size: 0.8em;
            font-weight: 600;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-right: 0px;
            cursor: pointer;
            
        }

        #reset-btn:hover {
            background-color: #1D223F;
            color: white;
        }

        .return-home {
            top: 20px; /* Add some spacing from the top */
            left: 20px; /* Add some spacing from the left */
            z-index: 10; /* Ensure it appears above other elements */
            margin-right: calc(100% - 580px) !important;
        }

        .back-button {
            position: relative;
            font-size: 0.8em;
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


    </style>

    </head>
    <body>
        <?php
        renderUnifiedComponent(
            '../img/iconpages/archive.png',
            'Archive',
            'Archive allows users to store, organize, and retrieve historical data or documents, providing easy access to past records.',
            'Announcement Archive',
            [
                ['label' => 'Pages', 'link' => '#'],
                ['label' => 'Archive', 'link' => '#'],
            ]

        );
        ?>
    
        <!-- Sort By Section -->
        <div class="sort-by-section">
            <!-- <select id="sortBy" class="sort-by-dropdown">
                <option value="" disabled selected>Select Creator Type</option>
                <option value="admin">Admin</option>
                <option value="brgyhead">Per Barangay (Brgy Head)</option>
            </select> -->

            <!-- back button to -->
            <div class="return-home">
                <a href="../pages/announcementpage" class="back-button">
                    <img src="../img/arrowback.png" alt="Return to Homepage" />
                    Return to Announcement
                </a>
            </div>

            <input type="text" id="date-range-input" placeholder="Date Range" readonly>
            <button id="reset-btn">Reset</button>

        </div>

        <div class="container">
            <div class="loob">
                <?php
                // Fetch and display dynamic content from the database
                include '../server/fetch_archiveA.php';

                // Loop through each grouped date
                foreach ($grouped_posts as $archived_date => $posts) {
                    ?>
                    <div class="post-container" data-date="<?php echo $archived_date; ?>"> <!-- Assuming `archive_at` is stored in the database -->
                        <!-- Your existing post HTML content -->
                        <div class="header">
                            <span class="date"><?php echo $archived_date; ?></span> <!-- Display archived date -->
                            <?php
                            // Loop through each post under the same archived date
                            foreach ($posts as $row) {
                                $created_at = date('F j, Y', strtotime($row['created_at']));
                                ?>
                                
                                <!-- Author and logo section inside one container -->
                                <div class="author-logo-container">
                                    <!-- Logo section -->
                                    <div class="logo-container">
                                        <?php
                                        // Static Logo for Admin
                                        if ($row['creator_type'] == 'admin') {
                                            echo '<img src="../img/LOGO/MDRRMO1.png" alt="Admin Logo" class="logo" />';
                                        }
                                        // Logo for brgyhead
                                        if ($row['creator_type'] == 'brgyhead' && !empty($row['img'])) {
                                            echo '<img src="data:image/jpeg;base64,' . base64_encode($row['img']) . '" alt="Barangay Logo" class="logo" />';
                                        }
                                        ?>
                                    </div>

                                    <!-- Author and post content section -->
                                    <div class="post-content">
                                        <span class="author">
                                            <?php
                                            // Display author name and created post information
                                            if ($row['creator_type'] == 'admin') {
                                                echo 'MDRRMO Cainta <span class="created-post" style="margin-left: 5px;">created a post</span>';
                                            } elseif ($row['creator_type'] == 'brgyhead') {
                                                echo 'Barangay ' . $row['BrgyName'] . ' <span class="created-post" style="margin-left: 5px;">created a post</span>';
                                            } else {
                                                echo $row['creator_type']; // Default to the creator_type
                                            }
                                            ?>
                                        </span>

                                        <div class="post-text">
                                            <p>
                                                <?php 
                                                // Capture the caption, escape special characters and make it safe for HTML rendering
                                                $caption = htmlspecialchars($row['caption']); 
                                                echo $caption; // Display caption from the database
                                                ?>
                                            </p>
                                        </div>

                                    </div>
                                </div>


                                <!-- Each post now gets its own "View" button and ellipsis -->
                                <div class="mamamo-actions">
                                    <div class="view-button" data-id="<?php echo $row['archiveA_id']; ?>">View</div>
                                </div>

                                <div class="archive">Created <?php echo $created_at; ?></div>

                            <?php
                            }
                            ?>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>

        <!-- Modal for Viewing Archived Announcement -->
        <div id="viewArchivedAnnouncementModal" class="modal hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Archived Announcement</h2>
                    <button id="closeModalButton" class="close-button">
                        <img src="../img/plus/closeD.png" alt="Close">
                    </button>
                </div>
                <!-- Changed from header-container to headerA-container -->
                <div class="headerA-container"></div>
                <div class="modal-body">
                    <div class="content-container">
                        <!-- Display Announcement Text -->
                        <textarea id="announcementText" class="announcement-input" readonly></textarea>

                        <!-- Media Preview Section inside the Modal -->
                        <div id="mediaPreviewA" class="media-preview"></div>
                    </div>
                </div>
                <!-- Footer with View and Delete Buttons (Optional) -->
                <div class="modal-footer"></div>
            </div>
        </div>






        <script>

            // Auto-adjust the height of the announcement input
            const announcementInput = document.querySelector(".announcement-input");
            announcementInput.addEventListener("input", function () {
            this.style.height = "auto"; // Reset height to auto to calculate new height
            this.style.height = this.scrollHeight + "px"; // Adjust height based on content
            });

            // Fetch user details for the header
            async function fetchUserDetails() {
            try {
                const response = await fetch(
                    "../server/get_AnnouncementA.php",
                );
                if (!response.ok) {
                    console.error(
                        `Failed to fetch user details: ${response.status} ${response.statusText}`,
                    );
                    return {
                        error: true,
                        message: `Error fetching details: ${response.statusText}`,
                    };
                }
                return await response.json();
            } catch (error) {
                console.error("Error in fetchUserDetails:", error);
                return {
                    error: true,
                    message: "Unexpected error occurred while fetching user details.",
                };
            }
            }

           

            
        // Function to open the archived announcement modal
        function openArchivedAnnouncementModal(announcementData) {
            const modal = document.getElementById('viewArchivedAnnouncementModal');
            const announcementTextArea = document.getElementById('announcementText');
            const mediaPreviewContainer = document.getElementById('mediaPreviewA');

            // Populate the modal with the announcement data
            announcementTextArea.value = announcementData.announcement.caption;
            announcementTextArea.style.height = `${announcementText.scrollHeight}px`;

            // Clear previous media previews
            mediaPreviewContainer.innerHTML = ''; 

            // Loop through media and add previews
            announcementData.media.forEach(mediaItem => {
                const mediaElement = document.createElement('div');
                mediaElement.classList.add('media-item'); // Add class for styling
                
                

                // Add media type handling (Image/Video)
                if (mediaItem.media_type === 'image') {
                    const img = document.createElement('img');
                    img.src = 'data:image/jpeg;base64,' + mediaItem.media_data;
                    img.alt = 'Image Preview';
                    img.classList.add('preview-image'); // Add image styling class
                    mediaElement.appendChild(img);
                } else if (mediaItem.media_type === 'video') {
                    const video = document.createElement('video');
                    video.controls = true;
                    const source = document.createElement('source');
                    source.src = 'data:video/mp4;base64,' + mediaItem.media_data;
                    source.type = 'video/mp4';
                    video.appendChild(source);
                    video.classList.add('preview-video'); // Add video styling class
                    mediaElement.appendChild(video);
                }

                // Append the remove button and media element to the container
                mediaPreviewContainer.appendChild(mediaElement);
            });

            // Show the modal by adding the 'visible' class
            modal.classList.add('visible');
            document.body.classList.add('modal-open'); // Prevent body scroll when modal is open
        }

            // Function to close the modal
            document.getElementById('closeModalButton').addEventListener('click', () => {
                const modal = document.getElementById('viewArchivedAnnouncementModal');
                modal.classList.remove('visible');
                document.body.classList.remove('modal-open'); // Enable body scroll when modal is closed
            });

            // Function to open the archived announcement modal
            document.querySelectorAll('.view-button').forEach(button => {
                button.addEventListener('click', function() {
                    const archiveA_id = this.getAttribute('data-id');  // Get the archiveA_id from the button's data-id
                    console.log('Fetching data for archiveA_id:', archiveA_id); // Debugging the ID value

                    if (archiveA_id) {
                        fetchArchivedAnnouncement(archiveA_id);  // Fetch announcement using the archiveA_id
                    } else {
                        console.error('No archiveA_id found.');
                    }
                });
            });


            // Function to fetch archived announcement details
            async function fetchArchivedAnnouncement(archiveA_id) {
                try {
                    // Fetch the data for the specific archiveA_id
                    const response = await fetch(`../server/get_AnnouncementA.php?id=${archiveA_id}`);
                    const data = await response.json();

                    if (data.error) {
                        console.error('Error fetching archived announcement:', data.message);
                        return;
                    }

                    // Extract announcement and creator details
                    const announcement = data.data.announcement;
                    const creator = data.data.creator;
                    const creatorName = creator.creator_name || "Unknown";  // Fallback to "Unknown" if creator name is missing
                    const logoPath = creator.logo_path || "../img/userlogo.png"; // Default logo if no logo found


                    // Setting up the header HTML structure dynamically
                    const headerHTML = `
                        <div class="headerA">
                            <img src="${logoPath}" alt="Profile Image" class="profile-image">
                            <div class="creator-details">
                                <h4 class="creator">${creatorName}</h4>
                                <p class="recipient">
                                    <img src="../img/userlogo.png" alt="Globe Icon" class="globe-icon">
                                    To everyone
                                </p>
                            </div>
                        </div>
                    `;

                    const headerContainer = document.querySelector(".headerA-container");
                    if (headerContainer) {
                        headerContainer.innerHTML = headerHTML;
                    } else {
                        console.error("Header container not found.");
                    }

                    // Call the function to open the modal with the announcement data
                    openArchivedAnnouncementModal(data.data);

                } catch (error) {
                    console.error('Error fetching archived announcement:', error);
                }
            }


            // Preview media files
            function previewMedia(files, type) {
            const previewContainer = document.getElementById("mediaPreviewA");

            // Clear existing previews if any
            previewContainer.innerHTML = "";

            Array.from(files).forEach((file) => {
                const reader = new FileReader();

                reader.onload = (event) => {
                    // Create a wrapper for the media item
                    const mediaWrapper = document.createElement("div");
                    mediaWrapper.className = "media-item";

                    // Add the media item to the wrapper
                    if (type === "image") {
                        const img = document.createElement("img");
                        img.src = event.target.result;
                        img.alt = "Selected Image";
                        img.className = "preview-image"; // Optional styling
                        mediaWrapper.appendChild(img);
                    } else if (type === "video") {
                        const video = document.createElement("video");
                        video.src = event.target.result;
                        video.controls = true;
                        video.className = "preview-video"; // Optional styling
                        mediaWrapper.appendChild(video);
                    }

                    // Append the 'X' button and media wrapper to the container
                    mediaWrapper.appendChild(xButton);
                    previewContainer.appendChild(mediaWrapper);
                };

                reader.readAsDataURL(file);
            });
            }

            // Preview media files
            function previewMedia(files, type) {
            const previewContainer = document.getElementById("mediaPreview");

            // Clear existing previews if any
            previewContainer.innerHTML = "";

            Array.from(files).forEach((file) => {
                const reader = new FileReader();

                reader.onload = (event) => {
                    // Create a wrapper for the media item
                    const mediaWrapper = document.createElement("div");
                    mediaWrapper.className = "media-item";

                    // Create the 'X' button
                    const xButton = document.createElement("button");
                    xButton.className = "x-button";
                    xButton.title = "Remove";

                    // Add an 'X' icon to the button
                    const closeIcon = document.createElement("img");
                    closeIcon.src = "../img/plus/closeD.png"; // Update the path as needed
                    closeIcon.alt = "Remove";
                    xButton.appendChild(closeIcon);

                    // Remove media item on 'X' button click
                    xButton.addEventListener("click", () => {
                        mediaWrapper.remove(); // Remove from the DOM

                        // Reset the file input so the file can be selected again
                        const input = document.getElementById(
                        type === "image" ? "imageInput" : "videoInput",
                        );
                        input.value = ""; // Reset the input
                    });

                    // Add the media item to the wrapper
                    if (type === "image") {
                        const img = document.createElement("img");
                        img.src = event.target.result;
                        img.alt = "Selected Image";
                        img.className = "preview-image"; // Optional styling
                        mediaWrapper.appendChild(img);
                    } else if (type === "video") {
                        const video = document.createElement("video");
                        video.src = event.target.result;
                        video.controls = true;
                        video.className = "preview-video"; // Optional styling
                        mediaWrapper.appendChild(video);
                    }

                    // Append the 'X' button and media wrapper to the container
                    mediaWrapper.appendChild(xButton);
                    previewContainer.appendChild(mediaWrapper);
                };

                reader.readAsDataURL(file);
            });
        }


        




















            // Initialize Flatpickr
            const dateRangeInput = flatpickr("#date-range-input", {
                mode: "range", // Enable date range selection
                dateFormat: "M d, Y", // Format the date as Jan 30, 2020 (e.g. 'Jan 30, 2020')
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        // Call the filtering function with the selected date range in "Jan 30, 2020" format
                        filterPostsByDate(selectedDates[0], selectedDates[1]);
                        document.getElementById('date-range-input').placeholder = 'Date Range'; // Change placeholder text
                        document.getElementById('date-range-input').classList.add('focused'); // Add focused styles
                        document.getElementById('date-range-input').style.textAlign = 'center';
                        document.getElementById('date-range-input').style.paddingLeft = '40px'; // Adjust position
                        document.getElementById('date-range-input').style.paddingRight = '50px'; // Adjust position
                    }
                },
            });

            // Function to filter posts by date
            function filterPostsByDate(startDate, endDate) {
                const posts = document.querySelectorAll(".post-container");  // Select all posts

                posts.forEach(post => {
                    // Use the 'data-date' attribute for comparison (which stores the 'archive_at' field)
                    const postDateStr = post.getAttribute("data-date");  // Get the date from data attribute
                    const postDate = new Date(postDateStr); // Convert the date string to a Date object

                    // Check if the post's date is within the selected range
                    if (postDate >= startDate && postDate <= endDate) {
                        post.style.display = "block"; // Show the post
                    } else {
                        post.style.display = "none"; // Hide the post
                    }
                });
            }

            // Function to reset the date range filter
            function resetDateRange() {
                // Clear the date range input
                dateRangeInput.clear();

                // Show all posts
                const posts = document.querySelectorAll(".post-container");
                posts.forEach(post => {
                    post.style.display = "block";
                });
            }

            // Add event listener to the reset button
            document.getElementById("reset-btn").addEventListener("click", resetDateRange);

            
        </script>

    </body>
    </html>
