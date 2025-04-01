// Call fetchAnnouncements() to load announcements when the page loads
fetchAnnouncements();

// Inject the button dynamically for this page
const systemSettings = document.querySelector(".system-settings");
if (systemSettings) {
    const button = document.createElement("button");
    button.className = "action-button";
    button.innerText = "Add Announcement";

    // Change the onclick event to open the modal
    button.onclick = () => {
        const modal = document.getElementById("createAnnouncementModal");
        if (modal) {
            modal.classList.add("visible"); // Show the modal
        } else {
            console.error("Modal element not found!");
        }

        // Disable page scrolling and interactions
        document.body.style.overflow = "hidden";
        const overlay = document.createElement("div");
        overlay.id = "overlay";
        overlay.classList.add("overlay");
        document.body.appendChild(overlay);
    };

    systemSettings.appendChild(button);
}

// Add hover effect to upload icons
document.querySelectorAll(".upload-icon img").forEach((img) => {
    const originalSrc = img.src; // Store the original image source
    const hoverSrc = img.getAttribute("data-hover"); // Get the hover image source

    // Add mouseenter event to change the image
    img.addEventListener("mouseenter", () => {
        if (hoverSrc) {
            img.src = hoverSrc;
        }
    });

    // Add mouseleave event to revert to the original image
    img.addEventListener("mouseleave", () => {
        img.src = originalSrc;
    });
});

// Fetch announcements dynamically
async function fetchAnnouncements() {
    try {
        const response = await fetch("../server/fetch_Announcement.php");
        const data = await response.json();

        const container = document.getElementById("media-container");
        if (container) {
            container.innerHTML = ""; // Clear existing content

            if (data.error) {
                container.innerHTML = `<p class="error-message">${data.message}</p>`;
                return;
            }

            data.data.forEach((announcement, index) => {
                const menuId = `menu-${index}`;
                const mediaDiv = document.createElement("div");
                mediaDiv.className = "announcement";

                mediaDiv.innerHTML = `
                <div class="header" style="position: relative;">
                    <img src="${announcement.creator_logo || "../img/userlogo.png"}" alt="Creator Logo" />
                    <div>
                        <h4 class="creator">${announcement.creator_name || "Unknown User"} ${announcement.creator_surname || ""}</h4>
                        <p class="announcement-date" data-date="${announcement.created_at_for_sorting}">${announcement.created_at || "Unknown Date"}</p>
                    </div>
                    <div class="menu-container">
                        <span class="menu-dots" onclick="toggleMenu(event, '${menuId}')">...</span>
                        <div id="${menuId}" class="dropdown-menu">
                            <a href="#" onclick="handleEdit(${announcement.announcement_id})">Edit</a>
                            <a href="#" onclick="handleDelete(${announcement.announcement_id})">Archive</a>
                            <a href="#" onclick="handleShare(${announcement.announcement_id})">Share</a>
                        </div>
                    </div>
                </div>
                <p class="caption">${announcement.caption || "No caption provided."}</p>
                <div class="announcement-media-container"></div>
                `;

                const mediaContainer = mediaDiv.querySelector(".announcement-media-container");

                if (announcement.media && announcement.media.length > 0) {
                    if (announcement.media.length === 1) {
                        mediaContainer.classList.add("single-item");
                    }

                    announcement.media.forEach((media) => {
                        if (media.type === "image") {
                            const img = document.createElement("img");
                            img.src = media.data;
                            img.alt = "Announcement Media";
                            mediaContainer.appendChild(img);
                        } else if (media.type === "video") {
                            const video = document.createElement("video");
                            video.src = media.data;
                            video.controls = true;
                            mediaContainer.appendChild(video);
                        }
                    });
                } else {
                    const noMedia = document.createElement("p");
                    noMedia.className = "no-media";
                    noMedia.textContent = "No media attached.";
                    mediaContainer.appendChild(noMedia);
                }

                container.appendChild(mediaDiv);
            });
        } else {
            console.error("Media container not found!");
        }
    } catch (error) {
        console.error("Error fetching announcements:", error);
        const container = document.getElementById("media-container");
        if (container) {
            container.innerHTML = '<p class="error-message">Failed to fetch announcements. Please try again later.</p>';
        }
    }
}

// Toggle dropdown menu visibility
function toggleMenu(event, menuId) {
    event.stopPropagation();
    const menu = document.getElementById(menuId);
    if (menu) {
        menu.classList.toggle("active");
    }
}

// Close all dropdown menus when clicking outside
document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu.active").forEach((menu) => menu.classList.remove("active"));
});

// Archive functionality with confirmation modal
async function handleDelete(announcementId) {
    try {
        const response = await fetch(`../server/check_authorization.php?announcement_id=${announcementId}`);
        const result = await response.json();

        if (result.status === 'error') {
            showMessage("error", result.message);
            return;
        }

        const archivePopupOverlay = document.getElementById("popupOverlayMAIN_ARCHIVE");
        if (!archivePopupOverlay) {
            console.error('Popup overlay not found!');
            return;
        }

        document.body.style.overflow = "hidden";
        const overlay = document.createElement("div");
        overlay.id = "overlay";
        overlay.classList.add("overlay");
        document.body.appendChild(overlay);

        archivePopupOverlay.style.display = "flex";
        setupArchiveConfirmation(announcementId);
    } catch (error) {
        console.error("Error during authorization or fetching:", error);
        showMessage("error", "An error occurred while processing your request.");
    }
}

function setupArchiveConfirmation(announcementId) {
    const proceedBtn = document.getElementById("proceedBtnMAIN_ARCHIVE");
    const cancelPopupBtn = document.getElementById("cancelPopupBtnMAIN_ARCHIVE");
    const archivePopupOverlay = document.getElementById("popupOverlayMAIN_ARCHIVE");

    if (!proceedBtn || !cancelPopupBtn) {
        console.error('Confirmation buttons not found!');
        return;
    }

    proceedBtn.addEventListener("click", async () => {
        try {
            const response = await fetch(`../server/archive_Announcement.php?announcement_id=${announcementId}`, {
                method: "POST",
            });

            const result = await response.json();

            if (result.status === "success") {
                showMessage("success", result.message);
                fetchAnnouncements();
            } else {
                showMessage("error", result.message);
            }
        } catch (error) {
            console.error("Error archiving announcement:", error);
            showMessage("error", "Failed to archive the announcement.");
        }

        archivePopupOverlay.style.display = "none";
        document.body.style.overflow = "";
        const overlay = document.getElementById("overlay");
        if (overlay) {
            overlay.remove();
        }
    });

    cancelPopupBtn.addEventListener("click", () => {
        archivePopupOverlay.style.display = "none";
        document.body.style.overflow = "";
        const overlay = document.getElementById("overlay");
        if (overlay) {
            overlay.remove();
        }
    });

    archivePopupOverlay.addEventListener("click", (e) => {
        if (e.target === archivePopupOverlay) {
            archivePopupOverlay.style.display = "none";
            document.body.style.overflow = "";
            const overlay = document.getElementById("overlay");
            if (overlay) {
                overlay.remove();
            }
        }
    });
}

// Show a success or error message
function showMessage(type, message) {
    const popup = document.getElementById("popupMessage");
    if (!popup) {
        console.error("Popup element not found!");
        return;
    }

    popup.className = `popup-message ${type}`;
    popup.innerText = message;
    popup.style.display = "block";
    popup.style.zIndex = 50000;

    setTimeout(() => {
        popup.style.display = "none";
    }, 3000);
}

// Auto-adjust the height of the announcement input
const announcementInput = document.querySelector(".announcement-input");
if (announcementInput) {
    announcementInput.addEventListener("input", function () {
        this.style.height = "auto";
        this.style.height = this.scrollHeight + "px";
    });
}

// Fetch user details for the header
async function fetchUserDetails() {
    try {
        const response = await fetch("../server/fetch_userAnnoucementDetails.php");
        if (!response.ok) {
            console.error(`Failed to fetch user details: ${response.status} ${response.statusText}`);
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

// Initialize the page with user details
async function init() {
    try {
        const userDetails = await fetchUserDetails();
        const { creatorName = "Undefined", logoPath = "../img/userlogo.png" } = userDetails || {};
        console.log("Creator Name:", creatorName, "Logo Path:", logoPath);

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
        document.querySelector(".header-container").innerHTML = headerHTML;
    } catch (error) {
        console.error("Error initializing the page:", error);
    }
}

init();

// Reset form and close modal
function resetFormAndCloseModal() {
    const modal = document.getElementById("createAnnouncementModal");
    const announcementText = document.getElementById("announcementText");

    if (!modal || !announcementText) {
        console.error("Modal or announcementText element is not defined.");
        return;
    }

    modal.classList.remove("visible");
    announcementText.value = "";
    announcementText.style.height = "";

    const mediaInputs = document.querySelectorAll("#mediaInputContainer input[type='file']");
    mediaInputs.forEach((input) => (input.value = ""));

    const mediaPreview = document.getElementById("mediaPreview");
    if (mediaPreview) {
        mediaPreview.innerHTML = "";
    }

    document.body.style.overflow = "";
    const overlay = document.getElementById("overlay");
    if (overlay) {
        overlay.remove();
    }
}

// Modal and button references
const modal = document.getElementById("createAnnouncementModal");
const openModalButton = document.querySelector(".action-button");
const closeModalButton = document.getElementById("closeModalButton");
const postButton = document.getElementById("postAnnouncementButton");
const mediaInput = document.getElementById("mediaInput");
const announcementText = document.getElementById("announcementText");

// Add event listener for the "Add Announcement" button
if (openModalButton) {
    openModalButton.onclick = () => {
        openModal("create");
    };
}

// Open modal with context (create or edit)
async function openModal(context, announcementData = {}) {
    const modal = document.getElementById("createAnnouncementModal");
    const modalHeader = document.querySelector("#createAnnouncementModal .modal-header h2");
    const mediaPreview = document.getElementById("mediaPreview");

    resetFormAndCloseModal();

    if (context === "edit") {
        modalHeader.textContent = "Edit Announcement";

        const announcementId = announcementData.announcement?.announcement_id;
        if (announcementId) {
            modal.setAttribute("data-announcement-id", announcementId);
        }

        const announcementText = document.getElementById("announcementText");
        announcementText.value = announcementData.announcement?.caption || "";
        announcementText.style.height = `${announcementText.scrollHeight}px`;

        toggleFileInputs(true);

        mediaPreview.innerHTML = "";
        if (announcementData.media && announcementData.media.length > 0) {
            announcementData.media.forEach((media) => {
                const mediaWrapper = document.createElement("div");
                mediaWrapper.className = "media-item";

                const mediaElement = document.createElement(media.media_type === "image" ? "img" : "video");
                mediaElement.src = `data:${media.mime_type};base64,${media.media_data}`;
                if (media.media_type === "video") mediaElement.controls = true;

                mediaWrapper.appendChild(mediaElement);
                mediaPreview.appendChild(mediaWrapper);
            });
        }
    } else {
        modalHeader.textContent = "Create Announcement";
        modal.removeAttribute("data-announcement-id");
        resetFormAndCloseModal();
        toggleFileInputs(false);
        mediaPreview.innerHTML = "";
    }

    modal.classList.add("visible");

    document.body.style.overflow = "hidden";
    const overlay = document.createElement("div");
    overlay.id = "overlay";
    overlay.classList.add("overlay");
    document.body.appendChild(overlay);
}

// Preview media files
function previewMedia(files, type) {
    const previewContainer = document.getElementById("mediaPreview");
    if (previewContainer) {
        previewContainer.innerHTML = "";

        Array.from(files).forEach((file) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                const mediaWrapper = document.createElement("div");
                mediaWrapper.className = "media-item";

                const xButton = document.createElement("button");
                xButton.className = "x-button";
                xButton.title = "Remove";

                const closeIcon = document.createElement("img");
                closeIcon.src = "../img/plus/closeD.png";
                closeIcon.alt = "Remove";
                xButton.appendChild(closeIcon);

                xButton.addEventListener("click", () => {
                    mediaWrapper.remove();
                    const input = document.getElementById(type === "image" ? "imageInput" : "videoInput");
                    input.value = "";
                });

                if (type === "image") {
                    const img = document.createElement("img");
                    img.src = event.target.result;
                    img.alt = "Selected Image";
                    img.className = "preview-image";
                    mediaWrapper.appendChild(img);
                } else if (type === "video") {
                    const video = document.createElement("video");
                    video.src = event.target.result;
                    video.controls = true;
                    video.className = "preview-video";
                    mediaWrapper.appendChild(video);
                }

                mediaWrapper.appendChild(xButton);
                previewContainer.appendChild(mediaWrapper);
            };

            reader.readAsDataURL(file);
        });
    }
}

// Attach event listeners for media preview
document.getElementById("imageInput")?.addEventListener("change", (event) => {
    previewMedia(event.target.files, "image");
});

document.getElementById("videoInput")?.addEventListener("change", (event) => {
    previewMedia(event.target.files, "video");
});

// Toggle file inputs (enable/disable)
function toggleFileInputs(disable) {
    const fileInputs = document.querySelectorAll("#createAnnouncementModal input[type='file']");
    const uploadIcons = document.querySelectorAll("#createAnnouncementModal .upload-icon");
    const uploadSection = document.querySelector("#createAnnouncementModal .media-upload");

    if (disable) {
        fileInputs.forEach((input) => {
            input.setAttribute("disabled", true);
            input.classList.add("disabled-placeholder");
        });
        uploadIcons.forEach((icon) => icon.classList.add("disabled"));
        uploadSection.classList.add("disabled");
    } else {
        fileInputs.forEach((input) => {
            input.removeAttribute("disabled");
            input.classList.remove("disabled-placeholder");
        });
        uploadIcons.forEach((icon) => icon.classList.remove("disabled"));
        uploadSection.classList.remove("disabled");
    }
}

// Fetch and handle announcement details for editing
async function handleEdit(announcementId) {
    try {
        const response = await fetch(`../server/get_Announcement.php?id=${announcementId}`);
        const rawResponse = await response.text();

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
            console.log("Fetched announcement data:", result.data);
            openModal("edit", result.data);
        }
    } catch (error) {
        console.error("An error occurred while fetching the announcement:", error);
        showMessage("error", "An unexpected error occurred. Please try again.");
    }
}

// Close modal
closeModalButton?.addEventListener("click", () => {
    modal.classList.remove("visible");
    document.body.style.overflow = "";
    const overlay = document.getElementById("overlay");
    if (overlay) overlay.remove();
});

// DOMContentLoaded event listener
document.addEventListener("DOMContentLoaded", () => {
    const popupOverlayMAIN = document.getElementById("popupOverlayMAIN");
    const announcementText = document.getElementById("announcementText");
    const modal = document.getElementById("createAnnouncementModal");

    // Post announcement
    postButton?.addEventListener("click", (e) => {
        e.preventDefault();

        const announcementTextValue = announcementText.value.trim();
        const mediaInputs = document.querySelectorAll("#mediaInputContainer input[type='file']");

        const errors = [];

        if (!announcementTextValue) errors.push("Announcement text is required.");

        if (errors.length > 0) {
            errors.forEach((error) => showMessage("error", error));
            return;
        }

        // Show the confirmation popup
        popupOverlayMAIN.style.display = "flex";
    });

    // Proceed with posting or editing announcement
    document.getElementById("proceedBtnMAIN")?.addEventListener("click", async () => {
        const modal = document.getElementById("createAnnouncementModal");
        const announcementId = modal.getAttribute("data-announcement-id");
        const announcementTextValue = document.getElementById("announcementText").value.trim();
        const mediaInputs = document.querySelectorAll("#mediaInputContainer input[type='file']");

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

        console.log("FormData to send:");
        console.log("text:", announcementTextValue);
        console.log("announcement_id:", announcementId);

        try {
            const endpoint = announcementId ? "../server/edit_Announcement.php" : "../server/post_Announcement.php";

            const response = await fetch(endpoint, {
                method: "POST",
                body: formData,
            });

            const rawResponse = await response.text();
            console.log("Raw Response:", rawResponse);

            const result = JSON.parse(rawResponse);

            if (!result.error) {
                const successMessage = announcementId ? "Announcement updated successfully." : "Announcement posted successfully.";
                showMessage("success", successMessage);

                resetFormAndCloseModal();
                fetchAnnouncements();
            } else {
                showMessage("error", result.message);
            }
        } catch (error) {
            console.error("Error processing announcement:", error);
            showMessage("error", "An error occurred while processing the announcement.");
        }

        // Hide the confirmation popup
        popupOverlayMAIN.style.display = "none";
    });

    // Cancel popup
    document.getElementById("cancelPopupBtnMAIN")?.addEventListener("click", () => {
        popupOverlayMAIN.style.display = "none";
    });

    // Function to display the popup message
    function showMessage(type, message) {
        const popup = document.getElementById("popupMessage");
        if (!popup) {
            console.error("Popup element not found!");
            return;
        }

        popup.className = `popup-message ${type}`;
        popup.innerText = message;
        popup.style.display = "block";
        popup.style.zIndex = 50000;

        setTimeout(() => {
            popup.style.display = "none";
        }, 3000);
    }
});

// Initialize Flatpickr
const dateRangeInput = flatpickr("#date-range-input", {
    mode: "range",
    dateFormat: "M d, Y",
    onChange: function(selectedDates, dateStr, instance) {
        if (selectedDates.length === 2) {
            filterPostsByDate(selectedDates[0], selectedDates[1]);
            document.getElementById('date-range-input').placeholder = 'Date Range';
            document.getElementById('date-range-input').classList.add('focused');
            document.getElementById('date-range-input').style.textAlign = 'center';
            document.getElementById('date-range-input').style.paddingLeft = '40px';
            document.getElementById('date-range-input').style.paddingRight = '50px';
        }
    },
});

// Function to filter posts by date
function filterPostsByDate(startDate, endDate) {
    const posts = document.querySelectorAll(".announcement");

    posts.forEach(post => {
        const postDateStr = post.querySelector(".announcement-date").dataset.date;
        const postDate = new Date(postDateStr);

        if (postDate >= startDate && postDate <= endDate) {
            post.style.display = "block";
        } else {
            post.style.display = "none";
        }
    });
}

// Function to reset the date range filter
function resetDateRange() {
    dateRangeInput.clear();
    const posts = document.querySelectorAll(".announcement");
    posts.forEach(post => {
        post.style.display = "block";
    });
}

// Add event listener to the reset button
document.getElementById("reset-btn")?.addEventListener("click", resetDateRange);

// document.getElementById("archived-btn")?.addEventListener("click", function() {
//     window.location.href = "../pages/archivepage";
// });
