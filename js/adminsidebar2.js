document.addEventListener('DOMContentLoaded', () => {
    const menuItems = document.querySelectorAll('.menu li');

    const alertPages = [
        'alertpage',
       'alertHistory',
    ];
    
    // Define the list of settings-related pages without the .php extension
    const settingsPages = [
        'settingspage',
        'landingeditorpage',
        'settingslandingMISSIONVISSION',
        'settingslandingCAINTACONTACTLIST',
        'settingslandingEMERCON',
        'messageSettings'
    ];

    const announcementPages = [
        'announcementpage',
        'archivepage',
    ];

    const reportPages = [
        'reportpage',
        'reportpageALERT',
    ];

    // Get the current page URL without the .php extension
    let currentPage = window.location.pathname;
    let currentPageWithoutExtension = currentPage.split('/').pop().replace('.php', ''); // Extract filename and remove .php

    // Log the current page for debugging
    console.log('Current Page:', currentPage);
    console.log('Current Page Without Extension:', currentPageWithoutExtension);

    menuItems.forEach((item) => {
        const section = item.getAttribute('data-section');
        if (!section) return;

        // Extract section filename from data-section and remove .php
        const sectionFileName = section.split('/').pop().replace('.php', ''); 

        // Log the section file name for debugging
        console.log('Section File Name:', sectionFileName);

        const img = item.querySelector('img');
        const defaultSrc = img.src;
        const hoverSrc = item.getAttribute('data-hover-icon');
        const selectedSrc = item.getAttribute('data-selected-icon');

        // Check if the current page matches the section or if the section is part of settings/announcement/report groups
        if (sectionFileName === currentPageWithoutExtension || 
            (alertPages.includes(currentPageWithoutExtension) && alertPages.includes(sectionFileName)) ||
            (settingsPages.includes(currentPageWithoutExtension) && settingsPages.includes(sectionFileName)) ||
            (announcementPages.includes(currentPageWithoutExtension) && announcementPages.includes(sectionFileName)) ||
            (reportPages.includes(currentPageWithoutExtension) && reportPages.includes(sectionFileName))) {

            item.classList.add('active');
            if (selectedSrc) img.src = selectedSrc;
        }

        // Hover effect
        item.addEventListener('mouseenter', () => {
            if (!item.classList.contains('active') && hoverSrc) {
                img.src = hoverSrc;
            }
        });

        item.addEventListener('mouseleave', () => {
            if (!item.classList.contains('active')) {
                img.src = defaultSrc;
            }
        });

        // Navigation on click
        item.addEventListener('click', () => {
            if (section) window.location.href = section;
        });
    });


        // Logo Preview Functionality
        const currentLogo = document.getElementById('current_logoUserCon');
        const browseButton = document.getElementById('browseUserConButton');
        const logoInput = document.getElementById('logoUserCon');
        const fileError = document.createElement('span');
        const logoMessage1 = document.getElementById('logoMessage1');

        browseButton.insertAdjacentElement('afterend', fileError);
        fileError.style.color = 'red';
        fileError.style.fontStyle = 'italic';

        browseButton.addEventListener('click', () => logoInput.click());

        logoInput.addEventListener('change', () => updateLogoPreviewEditProfile(logoInput));

        // Password toggle functionality
        const passwordToggles = document.querySelectorAll('.changePassword-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();

                // Get the associated password field using data-target
                const targetId = toggle.dataset.target;
                const passwordField = document.getElementById(targetId);

                if (passwordField) {
                    // Toggle between password and text input types
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        toggle.src = '../img/hide.png'; // Update to "hide" icon
                    } else {
                        passwordField.type = 'password';
                        toggle.src = '../img/show.png'; // Update to "show" icon
                    }
                } else {
                    console.error(`Password field with id "${targetId}" not found.`);
                }
            });
        });

    });



    /**
     * Updates the logo preview when a new file is selected.
     * @param {HTMLInputElement} input - The file input element.
     */
    function updateLogoPreviewEditProfile(input) {
        const previewElement = document.getElementById('current_logoUserCon');
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const logoMessage = document.getElementById('logoMessage1');

        if (input.files && input.files[0]) {
            const file = input.files[0];

            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Only JPEG, PNG, or GIF images are allowed.');
                input.value = ''; // Clear the file input
                if (previewElement) {
                    previewElement.style.display = 'none';
                    previewElement.src = ''; // Clear the preview image
                }
                logoMessage.textContent = 'Invalid file type. Please select a valid image.';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                if (previewElement) {
                    previewElement.style.display = 'block';
                    previewElement.src = e.target.result; // Set preview image source
                }
                logoMessage.textContent = ''; // Clear error message
            };

            reader.readAsDataURL(file); // Read the file as a data URL
        } else if (previewElement) {
            previewElement.style.display = 'none';
            previewElement.src = ''; // Clear the preview image
            logoMessage.textContent = 'No Logo Available';
        }
    }


    function logOut() {
        window.location.href = '../js/adminlogoutscript.php';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Get the Contact input field
        const contactInput = document.getElementById('contacts');
        // Get the Postal Code input field
        const postalCodeInput = document.getElementById('postal_code');
    
        if (contactInput) {
            // Ensure only numbers are allowed for the Contact input
            contactInput.addEventListener('input', (event) => {
                // Replace any non-numeric characters
                let currentValue = event.target.value;
                currentValue = currentValue.replace(/[^0-9]/g, ''); // Only numbers allowed
                event.target.value = currentValue; // Update the input value
            });
        }
    
        if (postalCodeInput) {
            // Ensure only numbers are allowed for the Postal Code input
            postalCodeInput.addEventListener('input', (event) => {
                // Replace any non-numeric characters
                let currentValue = event.target.value;
                currentValue = currentValue.replace(/[^0-9]/g, ''); // Only numbers allowed
                event.target.value = currentValue; // Update the input value
            });
        }
    });
    

    function openEditProfileModal() {
        const userId = document.getElementById('userId').getAttribute('data-userid');

        fetch(`../server/fetch_userData.php?userId=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);  // Show error message if there is any issue
                    return;
                }

                const fields = [
                    'username', 'email', 'firstname', 'middlename', 'surname',
                    'contacts', 'street', 'barangay', 'municipality', 'province', 'region', 'postal_code'
                ];

                fields.forEach(field => {
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        fieldElement.value = data[field] || ''; // Default to empty if no value is available
                    }
                });

                document.getElementById('email').value = data.email || '';
                document.getElementById('fullName').value = `${data.firstname || ''} ${data.middlename || ''} ${data.surname || ''}`.trim();
                document.getElementById('position').value = data.usertype === 'admin' ? 'MDRRMO Admin' : 'Staff Member';
                document.getElementById('passwordHint').value = data.password || '';

                const logoElement = document.getElementById('current_logoUserCon');
                const logoMessageElement = document.getElementById('logoMessage1');

                if (data.img) {
                    logoElement.src = `data:image/png;base64,${data.img}`;
                    logoMessageElement.style.display = 'none';
                } else {
                    logoElement.src = '';
                    logoElement.style.display = 'none';
                    logoMessageElement.style.display = 'inline';
                }

                document.getElementById('edit-profile-modal').classList.remove('hidden');
                document.getElementById('edit-profile-modal').classList.add('show');
                document.body.classList.add('modal-open');
            })
            .catch(error => console.error('Error fetching user data:', error));
    }

    function closeEditProfile() {
        document.getElementById('edit-profile-modal').classList.remove('show');
        document.getElementById('edit-profile-modal').classList.add('hidden');
        document.body.classList.remove('modal-open');
    }


    function openChangePasswordModal() {
        document.getElementById('change-password-modal').classList.add('show');
    }

    function closeChangePasswordModal() {
        document.getElementById('change-password-modal').classList.remove('show');
    }

    const updateProfileBtn = document.getElementById('updateProfileBtn');

    updateProfileBtn.addEventListener('click', function () {
        const userIdElement = document.getElementById('userId');
        const userId = userIdElement ? userIdElement.getAttribute('data-userid') : null;
        

        if (!userId) {
            console.error('Error: userId not found.');
            showMessage('error', 'Error: Unable to find userId. Please try again or contact support.');
            return;
        }

        const formData = new FormData();
        const fields = [
            'username', 'firstname', 'middlename', 'surname', 'contacts',
            'email', 'street', 'barangay', 'municipality', 'province', 'region', 'postal_code'
        ];

        let allFieldsValid = true;
        fields.forEach(field => {
            const value = document.getElementById(field).value.trim();
            
            // Skip validation for 'middlename'
            if (!value && field !== 'middlename') {
                allFieldsValid = false;
            }
        
            // Include 'middlename' in formData even if empty
            formData.append(field, value || '');
        });    

        if (!allFieldsValid) {
            showMessage('error', 'All fields are required.');
            return;
        }

        const logoInput = document.getElementById('logoUserCon');
        if (logoInput.files.length > 0) {
            formData.append('logo', logoInput.files[0]);
        }

        formData.append('userId', userId); // Include userId in the request

        const popupOverlay = document.getElementById('popupOverlay');
        const proceedBtn = document.getElementById('proceedBtn');
        const cancelPopupBtn = document.getElementById('cancelPopupBtn');

        popupOverlay.style.display = 'flex';

        proceedBtn.onclick = () => {
            fetch('../server/update_userData.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text()) // Expect plain text response
                .then(message => {
                    if (message.startsWith('Success:')) {
                        showMessage('success', message.replace('Success:', '').trim());

                        // Fetch updated name from the form
                        const updatedFullName = `${document.getElementById('firstname').value.trim()} ${document.getElementById('middlename').value.trim()} ${document.getElementById('surname').value.trim()}`.trim();

                        // Dynamically update the sidebar profile
                        updateSidebarProfile(updatedFullName);

                        
                        // Hide the Edit Profile Modal
                        const editProfileModal = document.getElementById('edit-profile-modal');
                        if (editProfileModal) {
                            editProfileModal.classList.add('hidden');
                            editProfileModal.classList.remove('show');
                            console.log('Edit Profile Modal hidden.');
                        }

                    } else if (message.startsWith('Error:')) {
                        showMessage('error', message.replace('Error:', '').trim());
                    } else {
                        showMessage('error', 'Unexpected server response.');
                    }
                })
                .catch(error => {
                    console.error('Error updating:', error);
                    showMessage('error', 'An unexpected error occurred while updating the profile.');
                });

            popupOverlay.style.display = 'none';
        };

        cancelPopupBtn.onclick = () => {
            popupOverlay.style.display = 'none';
            
        };
    });

    /**
     * Updates the sidebar profile image and name dynamically after a successful update.
     * @param {string} updatedName - The updated full name of the user.
     */
    function updateSidebarProfile(updatedName) {
        // Update profile image
        const logoInput = document.getElementById('logoUserCon');
        const sidebarProfileImage = document.getElementById('sidebarProfileImage');

        if (logoInput.files && logoInput.files[0]) {
            const file = logoInput.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                if (sidebarProfileImage) {
                    sidebarProfileImage.src = e.target.result; // Update the image source
                    console.log('Sidebar profile image updated dynamically.');
                }
            };

            reader.readAsDataURL(file); // Read the uploaded file as a data URL
        }

        const userIdElement = document.getElementById('userId');
        const userId = userIdElement ? userIdElement.getAttribute('data-userid') : null;

        // Update profile name
        const sidebarUserName = document.getElementById('sidebarUserName');
        if (sidebarUserName && updatedName) {
            sidebarUserName.textContent = updatedName;
            console.log('Sidebar user name updated dynamically.');
        }

        // Send the updated name to the server to update the session
        fetch('../server/update_userData.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                userId: userId, // Ensure this is not null
                updatedName: updatedName,
            }),
        })
            .then(response => response.text())
            .then(message => {
                if (message.startsWith('Success:')) {
                    console.log('Session updated with new name:', updatedName);
                } else {
                    console.error('Failed to update session:', message);
                }
            })
            .catch(error => {
                console.error('Error updating session:', error);
            });
    }



    document.addEventListener('DOMContentLoaded', () => {
        const formPass = document.getElementById('changePasswordForm');
        const popupOverlayPass = document.getElementById('popupOverlay');
        const proceedBtnPass = document.getElementById('proceedBtn');
        const cancelPopupBtnPass = document.getElementById('cancelPopupBtn');

        if (!formPass) {
            console.error('Form not found in DOM!');
            return;
        }

        console.log('Form found! Attaching submit event listener.');

        // Save button triggers validation and popup confirmation
        const saveButton = document.getElementById('saveChangesBtn');
        saveButton.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default form submission

            // Get the input values
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            // Check if all fields are filled
            if (!currentPassword || !newPassword || !confirmPassword) {
                showMessage('error', 'All fields are required.');
                return;
            }

            // Validate the new password
            const passwordPattern = /^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d).{8,}$/;
            if (!passwordPattern.test(newPassword)) {
                showMessage('error', 'Password must contain at least 1 uppercase letter, 1 special character, 1 number, and be 8 characters or longer.');
                return;
            }

            // Check if passwords match
            if (newPassword !== confirmPassword) {
                showMessage('error', 'Passwords do not match!');
                return;
            }

            // If validation passes, show the confirmation popup
            popupOverlayPass.style.display = 'flex';
        });


        // Proceed button in the popup confirmation
        proceedBtnPass.onclick = async () => {
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                showMessage('error', 'All fields are required.');
                popupOverlayPass.style.display = 'none'; // Hide popup
                return;
            }

            if (newPassword !== confirmPassword) {
                showMessage('error', 'Passwords do not match!');
                popupOverlayPass.style.display = 'none'; // Hide popup
                return;
            }

            try {
                console.log('Sending request to server...');
                const response = await fetch('../server/update_userPassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        current_password: currentPassword,
                        new_password: newPassword,
                    }),
                });

                const result = await response.text(); // Read plain text response
                console.log('Server response:', result);

                if (result === 'Password updated successfully.') {
                    showMessage('success', result);

                    // Hide the Change Password Modal
                    const changePasswordModal = document.getElementById('change-password-modal');
                    if (changePasswordModal) {
                        changePasswordModal.classList.add('hidden');
                        changePasswordModal.classList.remove('show');
                        console.log('Change Password Modal hidden.');
                    }

                    // Reset Change Password Modal fields
                    document.getElementById('current-password').value = '';
                    document.getElementById('new-password').value = '';
                    document.getElementById('confirm-password').value = '';

                    // Ensure the modal overlay is removed
                    document.body.classList.remove('modal-open');

                    // Hide the Edit Profile Modal
                    const editProfileModal = document.getElementById('edit-profile-modal');
                    if (editProfileModal) {
                        editProfileModal.classList.add('hidden');
                        editProfileModal.classList.remove('show');
                        console.log('Edit Profile Modal hidden.');
                    }

                } else {
                    showMessage('error', result || 'Failed to update password.');
                    document.getElementById('current-password').value = '';
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('error', 'An error occurred. Please try again later.');
            }

            popupOverlayPass.style.display = 'none'; // Hide popup after submitting
        };

        // Cancel button in the popup confirmation
        cancelPopupBtnPass.onclick = () => {
            popupOverlayPass.style.display = 'none'; // Hide popup on cancel
        };
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

        popup.className = `popup-message ${type}`;
        popup.innerText = message;
        popup.style.display = 'block';
        popup.style.zIndex = 50000;

        // Automatically hide the popup after 3 seconds
        setTimeout(() => {
            popup.style.display = 'none';
        }, 3000);
    }
