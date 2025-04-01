// Inject the button dynamically for this page
const systemSettings = document.querySelector('.system-settings');
if (systemSettings) {
    const button = document.createElement('button');
    button.className = 'action-button';
    button.innerText = 'Add Announcement';

    // Change the onclick event to open the modal
    button.onclick = () => {
        const modal = document.getElementById('createAnnouncementModal');
        if (modal) {
            modal.classList.add('visible'); // Show the modal
        }
    };

    systemSettings.appendChild(button);
}

document.querySelectorAll('.upload-icon img').forEach(img => {
    const originalSrc = img.src; // Store the original image source
    const hoverSrc = img.getAttribute('data-hover'); // Get the hover image source

    // Add mouseenter event to change the image
    img.addEventListener('mouseenter', () => {
        if (hoverSrc) {
            img.src = hoverSrc;
        }
    });

    // Add mouseleave event to revert to the original image
    img.addEventListener('mouseleave', () => {
        img.src = originalSrc;
    });
});

// Fetch announcements dynamically
async function fetchAnnouncements() {

    try {
        const response = await fetch('../server/fetch_Announcement.php');
        const data = await response.json();

        const container = document.getElementById('media-container');
        container.innerHTML = ''; // Clear existing content

        if (data.error) {
            container.innerHTML = `<p class="error-message">${data.message}</p>`;
            return;
        }

        data.data.forEach((announcement, index) => {
            const menuId = `menu-${index}`;
            const mediaDiv = document.createElement('div');
            mediaDiv.className = 'announcement';

            mediaDiv.innerHTML = `
                <div class="header" style="position: relative;">
                    <img src="${announcement.creator_logo || '../img/userlogo.png'}" alt="Creator Logo" />
                    <div>
                        <h4 class="creator">${announcement.creator_name || 'Unknown User'} ${announcement.creator_surname || ''}</h4>
                        <p class="announcement-date">${announcement.created_at || 'Unknown Date'}</p>
                    </div>
                    <div class="menu-container">
                        <span class="menu-dots" onclick="toggleMenu(event, '${menuId}')">...</span>
                        <div id="${menuId}" class="dropdown-menu">
                            <a href="#" onclick="handleEdit(${announcement.announcement_id})">Edit</a>
                            <a href="#" onclick="handleDelete(${announcement.announcement_id})">Delete</a>
                        </div>
                    </div>
                </div>
                <p class="caption">${announcement.caption || 'No caption provided.'}</p>
                <div class="announcement-media-container"></div>
            `;

            const mediaContainer = mediaDiv.querySelector('.announcement-media-container');

            if (announcement.media && announcement.media.length > 0) {
                if (announcement.media.length === 1) {
                    mediaContainer.classList.add('single-item');
                }

                announcement.media.forEach((media) => {
                    if (media.type === 'image') {
                        const img = document.createElement('img');
                        img.src = media.data;
                        img.alt = 'Announcement Media';
                        mediaContainer.appendChild(img);
                    } else if (media.type === 'video') {
                        const video = document.createElement('video');
                        video.src = media.data;
                        video.controls = true;
                        mediaContainer.appendChild(video);
                    }
                });
            } else {
                const noMedia = document.createElement('p');
                noMedia.className = 'no-media';
                noMedia.textContent = 'No media attached.';
                mediaContainer.appendChild(noMedia);
            }

            container.appendChild(mediaDiv);
        });
    } catch (error) {
        console.error('Error fetching announcements:', error);
        const container = document.getElementById('media-container');
        container.innerHTML = '<p class="error-message">Failed to fetch announcements. Please try again later.</p>';
    }
}

function toggleMenu(event, menuId) {
    event.stopPropagation();
    const menu = document.getElementById(menuId);
    menu.classList.toggle('active');
}

document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu.active').forEach(menu => menu.classList.remove('active'));
});

// Delete functionality
async function handleDelete(announcementId) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        try {
            const response = await fetch(`../server/delete_Announcement.php?id=${announcementId}`, { method: 'POST' });
            const result = await response.json();
            if (result.success) {
                fetchAnnouncements(); // Refresh announcement list
            } else {
                showMessage('error', result.message);
            }
        } catch (error) {
            console.error('Error deleting announcement:', error);
            showMessage('error', 'Failed to delete announcement.');
        }
    }
}

fetchAnnouncements();


// Auto-adjust the height of the announcement input
const announcementInput = document.querySelector('.announcement-input');
announcementInput.addEventListener('input', function () {
    this.style.height = 'auto'; // Reset height to auto to calculate new height
    this.style.height = this.scrollHeight + 'px'; // Adjust height based on content
});


async function fetchUserDetails() {
    try {
        const response = await fetch('../server/fetch_userAnnoucementDetails.php');
        if (!response.ok) {
            console.error(`Failed to fetch user details: ${response.status} ${response.statusText}`);
            return { error: true, message: `Error fetching details: ${response.statusText}` };
        }
        return await response.json();
    } catch (error) {
        console.error('Error in fetchUserDetails:', error);
        return { error: true, message: 'Unexpected error occurred while fetching user details.' };
    }
}


async function init() {
    try {
        const userDetails = await fetchUserDetails();

        // Safeguard against undefined values
        const { creatorName = 'Undefined', logoPath = '../img/userlogo.png' } = userDetails || {};
        console.log('Creator Name:', creatorName, 'Logo Path:', logoPath);

        const headerHTML = `
            <div class="header">
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
        document.querySelector('.header-container').innerHTML = headerHTML;
    } catch (error) {
        console.error('Error initializing the page:', error);
    }
}


init();


function showLoader() {
    document.getElementById('loader').classList.remove('hidden');
}

function hideLoader() {
    setTimeout(() => {
        document.getElementById('loader').classList.add('hidden');
    }, 100); // Delay of 2 seconds
}
        

// Move the resetFormAndCloseModal function to the top
function resetFormAndCloseModal() {
    const modal = document.getElementById("createAnnouncementModal");
    const announcementText = document.getElementById("announcementText");

    if (!modal || !announcementText) {
        console.error("Modal or announcementText element is not defined.");
        return;
    }

    modal.classList.remove("visible");
    announcementText.value = ""; // Clear the text
    announcementText.style.height = ""; // Reset the height to its default

    // Clear all media inputs and previews
    const mediaInputs = document.querySelectorAll("#mediaInputContainer input[type='file']");
    mediaInputs.forEach((input) => input.value = "");

    const mediaPreview = document.getElementById("mediaPreview");
    if (mediaPreview) {
        mediaPreview.innerHTML = "";
    }
}


const modal = document.getElementById('createAnnouncementModal');
const openModalButton = document.querySelector('.action-button');
const closeModalButton = document.getElementById('closeModalButton');
const postButton = document.getElementById('postAnnouncementButton');
const mediaInput = document.getElementById('mediaInput');
const announcementText = document.getElementById('announcementText');

openModalButton.addEventListener("click", () => {
    openModal("create");
});



function openModal(context, announcementData = {}) {
    const modal = document.getElementById("createAnnouncementModal");
    const modalHeader = document.querySelector("#createAnnouncementModal .modal-header h2");
    const mediaPreview = document.getElementById("mediaPreview");

    if (context === "edit") {
        modalHeader.textContent = "Edit Announcement";

        // Extract `announcement_id` from `announcementData`
        const announcementId = announcementData.announcement?.announcement_id;
        if (announcementId) {
            modal.setAttribute("data-announcement-id", announcementId);
        }

        // Populate announcement text
        const announcementText = document.getElementById("announcementText");
        announcementText.value = announcementData.announcement?.caption || "";
        announcementText.style.height = `${announcementText.scrollHeight}px`;

        // Disable file input fields in edit mode
        toggleFileInputs(true);

        // Populate media preview
        mediaPreview.innerHTML = ""; // Clear existing content first
        if (announcementData.media && announcementData.media.length > 0) {
            announcementData.media.forEach((media) => {
                const mediaWrapper = document.createElement("div");
                mediaWrapper.className = "media-item";

                // Create the 'X' button for removing media
                const xButton = document.createElement("button");
                xButton.className = "x-button";
                xButton.title = "Remove";
                const closeIcon = document.createElement("img");
                closeIcon.src = "../img/plus/closeD.png"; // Update path if needed
                closeIcon.alt = "Remove";
                xButton.appendChild(closeIcon);

                // Disable removal in edit mode if needed
                xButton.addEventListener("click", () => {
                    mediaWrapper.remove();
                });

                // Create the media element (image or video)
                const mediaElement = document.createElement(media.media_type === "image" ? "img" : "video");
                mediaElement.src = `data:${media.mime_type};base64,${media.media_data}`;
                if (media.media_type === "video") mediaElement.controls = true;

                // Append everything to the wrapper
                mediaWrapper.appendChild(mediaElement);
                mediaWrapper.appendChild(xButton);

                // Append the media wrapper to the preview container
                mediaPreview.appendChild(mediaWrapper);
            });
        }
    } else {
        modalHeader.textContent = "Create Announcement";
        modal.removeAttribute("data-announcement-id");
        resetFormAndCloseModal();
        toggleFileInputs(false);
        mediaPreview.innerHTML = ""; // Clear media preview for new announcements
    }
    modal.classList.add("visible");
}


function toggleFileInputs(disable) {
    const fileInputs = document.querySelectorAll("#createAnnouncementModal input[type='file']");
    const uploadIcons = document.querySelectorAll("#createAnnouncementModal .upload-icon");
    const uploadSection = document.querySelector("#createAnnouncementModal .media-upload");

    if (disable) {
        // Add a class or directly hide elements
        fileInputs.forEach((input) => {
            input.setAttribute("disabled", true); // Disable input
            input.classList.add("disabled-placeholder"); // Optional, add a class for styling
        });
        uploadIcons.forEach((icon) => icon.classList.add("disabled"));
        uploadSection.classList.add("disabled"); // Disable container
    } else {
        // Enable or show elements
        fileInputs.forEach((input) => {
            input.removeAttribute("disabled");
            input.classList.remove("disabled-placeholder");
        });
        uploadIcons.forEach((icon) => icon.classList.remove("disabled"));
        uploadSection.classList.remove("disabled");
    }
}


// Function to fetch and handle announcement details
async function handleEdit(announcementId) {
    try {
        const response = await fetch(`../server/get_Announcement.php?id=${announcementId}`);
        const rawResponse = await response.text();

        // Check if the response is valid JSON
        let result;
        try {
            result = JSON.parse(rawResponse);
        } catch (parseError) {
            console.error("Invalid JSON response:", rawResponse);
            throw new Error("Invalid server response");
        }

        if (result.error) {
            showMessage("error", result.message);
        } else {
            console.log("Fetched announcement data:", result.data); // Debug log
            openModal("edit", result.data); // Pass the entire `result.data` object
        }
    } catch (error) {
        console.error("An error occurred while fetching the announcement:", error);
        showMessage("error", "An unexpected error occurred. Please try again.");
    }
}






// Close modal
closeModalButton.addEventListener('click', () => {
    modal.classList.remove('visible');

});


document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById("loader");
    const postButton = document.getElementById("postAnnouncementButton");
    const popupOverlay = document.getElementById("popupOverlay");
    const announcementText = document.getElementById("announcementText");
    const modal = document.getElementById("createAnnouncementModal");

    // Post announcement
    postButton.addEventListener("click", (e) => {
        e.preventDefault(); // Prevent default action

        const announcementTextValue = announcementText.value.trim();
        const mediaInputs = document.querySelectorAll("#mediaInputContainer input[type='file']");

        const errors = [];

        if (!announcementTextValue) errors.push("Announcement text is required.");

        if (errors.length > 0) {
            errors.forEach((error) => showMessage("error", error));
            return;
        }

        popupOverlay.style.display = "flex"; // Show confirmation popup
    });



    document.getElementById("proceedBtn").addEventListener("click", async () => {
        showLoader();

        const modal = document.getElementById("createAnnouncementModal");
        const announcementId = modal.getAttribute("data-announcement-id");
        const announcementTextValue = document.getElementById("announcementText").value.trim();
        const mediaInputs = document.querySelectorAll("#mediaInputContainer input[type='file']"); // Ensure this is defined

        const formData = new FormData();
        formData.append("text", announcementTextValue);

        if (announcementId) {
            formData.append("announcement_id", announcementId);
        }

        mediaInputs.forEach((input) => {
            for (const file of input.files) {
                formData.append("media[]", file);
            }
        });

        // Debugging the FormData
        console.log("FormData to send:");
        console.log("text:", announcementTextValue);
        console.log("announcement_id:", announcementId);

        try {
            const endpoint = announcementId
                ? "../server/edit_Announcement.php"
                : "../server/post_Announcement.php";

            const response = await fetch(endpoint, {
                method: "POST",
                body: formData,
            });

            const rawResponse = await response.text();
            console.log("Raw Response:", rawResponse);

            const result = JSON.parse(rawResponse);

            if (!result.error) {
                const successMessage = announcementId
                    ? "Announcement updated successfully."
                    : "Announcement posted successfully.";
                showMessage("success", successMessage);

                resetFormAndCloseModal();
                fetchAnnouncements();
            } else {
                showMessage("error", result.message);
            }
        } catch (error) {
            console.error("Error processing announcement:", error);
            showMessage("error", "An error occurred while processing the announcement.");
        } finally {
            hideLoader();
        }
    });

    document.getElementById("cancelPopupBtn").addEventListener("click", () => {
        popupOverlay.style.display = "none"; // Hide confirmation popup
    });

    /**
     * Function to display the popup message.
     * @param {string} type - The type of message: 'success' or 'error'.
     * @param {string} message - The message to display.
     */
    function showMessage(type, message) {
        const popup = document.getElementById('popupMessage');
        if (!popup) {
            console.error('Popup element not found!');
            return;
        }

        // Update popup classes and content
        popup.className = `popup-message ${type}`;
        popup.innerText = message; // Display the message without any prefix
        popup.style.display = 'block';
        popup.style.zIndex = 50000;

        // Automatically hide the popup after 3 seconds
        setTimeout(() => {
            popup.style.display = 'none';
        }, 3000);
    }


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
        const input = document.getElementById(type === "image" ? "imageInput" : "videoInput");
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

// Now attach the event listener after the function definition
document.getElementById("imageInput").addEventListener("change", (event) => {
previewMedia(event.target.files, "image");
});

document.getElementById("videoInput").addEventListener("change", (event) => {
previewMedia(event.target.files, "video");
});


});



        
