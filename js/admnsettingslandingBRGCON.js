// Select the "Select All" checkbox and all row checkboxes
const selectAllCheckbox = document.getElementById('select-all');
const rowCheckboxes = document.querySelectorAll('.row-checkbox');

// Add event listener to "Select All" checkbox
selectAllCheckbox.addEventListener('change', function () {
    const isChecked = selectAllCheckbox.checked;

    // Set all row checkboxes to the same state as "Select All"
    rowCheckboxes.forEach(function (checkbox) {
        checkbox.checked = isChecked;
    });
});

// Optionally: Add event listeners to row checkboxes to sync with "Select All"
rowCheckboxes.forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        if (!checkbox.checked) {
            // If any row checkbox is unchecked, uncheck "Select All"
            selectAllCheckbox.checked = false;
        } else if (Array.from(rowCheckboxes).every(cb => cb.checked)) {
            // If all row checkboxes are checked, check "Select All"
            selectAllCheckbox.checked = true;
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    // Get the contact number input field
    const contactNumberInput = document.getElementById('contact_number');

    if (contactNumberInput) {
        contactNumberInput.addEventListener('input', (event) => {
            // Get the current value of the contact number input
            let currentValue = event.target.value;

            // Replace all alphabetic characters with an empty string (allow all symbols, but not letters)
            currentValue = currentValue.replace(/[A-Za-z]/g, '');

            // Update the input value with the cleaned string
            event.target.value = currentValue;
        });
    }
});



document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editModal');
    const closeBtn = document.querySelector('.close');
    const form = document.getElementById('editForm');
    const currentLogo = document.getElementById('current_logo');
    const browseButton = document.getElementById('browseButton');
    const logoInput = document.getElementById('logoBRGCON'); // The hidden file input
    const logoMessage = document.getElementById('logoMessage');
    const fileError = document.createElement('span'); // Create a span for file errors
    const addAccountButton = document.querySelector('.add-account-btn'); // Add button
    const modalTitle = document.getElementById('modalTitle'); // Modal title element
    const submitBtn = document.getElementById('submitBtn'); // The submit button
    const confirmationOverlay = document.getElementById('confirmationOverlay');
    const confirmAction = document.getElementById('confirmAction');
    const cancelAction = document.getElementById('cancelAction');

    // Function to open the modal
    function openModal(title, isAdding = false) {
        modalTitle.textContent = title; // Set the title based on the action
        submitBtn.textContent = isAdding ? "Add" : "Update"; // Change button text based on action
        modal.style.display = 'block';
        document.body.classList.add('modal-open'); // Disable scrolling
    }

    // Function to close the modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open'); // Enable scrolling
    }

    // Clear form inputs for adding new contact
    function clearForm() {
        document.getElementById('contact_id').value = ''; // Clear hidden ID field
        document.getElementById('barangay_name').value = '';
        document.getElementById('punong_barangay').value = '';
        document.getElementById('contact_number').value = '';
        document.getElementById('email').value = '';
        document.getElementById('address').value = '';
        currentLogo.style.display = 'none';
        logoMessage.textContent = 'No logo available';
        fileError.textContent = ''; // Clear file error
    }

    // Open modal for adding a new contact (Clear form)
    addAccountButton.addEventListener('click', function () {
        clearForm(); // Clear form for adding
        openModal("Add Barangay Contact", true); // Show the modal with the correct title and button text
    });

    // Attach click event to edit buttons
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            const row = this.closest('tr');
            const id = row.querySelector('td:nth-child(2)').textContent.trim();
            const barangayName = row.querySelector('td:nth-child(3)').textContent.trim();
            const punongBarangay = row.querySelector('td:nth-child(4)').textContent.trim();
            const contactNumber = row.querySelector('td:nth-child(5)').textContent.trim();
            const email = row.querySelector('td:nth-child(6)').textContent.trim();

            console.log('Email:', email); // Verify the extracted email
            const address = row.querySelector('td:nth-child(7)').textContent.trim();

            // Extract logo link if available
            const logo = row.querySelector('td:nth-child(8) a') ? row.querySelector('td:nth-child(8) a').href : null;

            // Populate modal inputs for editing
            document.getElementById('contact_id').value = id;
            document.getElementById('barangay_name').value = barangayName;
            document.getElementById('punong_barangay').value = punongBarangay;
            document.getElementById('contact_number').value = contactNumber;
            document.getElementById('email_modal').value = email;
            document.getElementById('address').value = address;

            // Set logo if available
            if (logo) {
                currentLogo.src = logo;
                currentLogo.style.display = 'block';
                logoMessage.textContent = ''; // Clear the message
            } else {
                currentLogo.style.display = 'none';
                logoMessage.textContent = 'No logo available'; // Set fallback text
            }

            // Show modal for editing with the correct title and button text
            openModal("Edit Barangay Contact", false);
        });
    });

    // Close modal when "x" is clicked
    closeBtn.onclick = function () {
        closeModal();
    };

    // Close modal when clicking outside of it
    window.onclick = function (event) {
        if (event.target == modal) {
            closeModal();
        }
    };

    // Trigger the file input dialog when the "Browse" button is clicked
    browseButton.addEventListener('click', function () {
        logoInput.click();
    });

    // Handle file selection, validate file type, and update the logo preview
    logoInput.addEventListener('change', function () {
        const [file] = logoInput.files;
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        // Clear previous error and preview
        fileError.textContent = '';
        currentLogo.style.display = 'none';

        if (file) {
            if (!allowedTypes.includes(file.type)) {
                fileError.textContent = 'Invalid file type. Only JPEG, PNG, or GIF images are allowed.';
                logoInput.value = ''; // Clear the invalid input
                return;
            }

            currentLogo.src = URL.createObjectURL(file);
            currentLogo.style.display = 'block'; // Show the preview
            logoMessage.textContent = ''; // Clear any additional messages
        }
    });

    // Show the confirmation overlay when submitBtn is clicked
    submitBtn.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting immediately
        confirmationOverlay.style.display = 'flex';
        document.body.classList.add('no-scroll'); // Disable scrolling
    });

    // Handle the confirm action
    confirmAction.addEventListener('click', function() {
        form.submit(); // Submit the form
        confirmationOverlay.style.display = 'none';
        document.body.classList.remove('no-scroll'); // Enable scrolling
    });

    // Handle the cancel action
    cancelAction.addEventListener('click', function() {
        confirmationOverlay.style.display = 'none';
        document.body.classList.remove('no-scroll'); // Enable scrolling
    });
});


document.addEventListener('DOMContentLoaded', function () {
    displayNotification();
});

function displayNotification() {
    // Check if there are success or error messages stored in sessionStorage
    const successMessage = sessionStorage.getItem('success');
    const errorMessage = sessionStorage.getItem('error');

    // If success message exists, show the success notification
    if (successMessage) {
        showNotification('success', successMessage);
        sessionStorage.removeItem('success');  // Clear the success message after displaying

    // If error message exists, show the error notification
    } else if (errorMessage) {
        showNotification('error', errorMessage);
        sessionStorage.removeItem('error');  // Clear the error message after displaying
    }
}

function showNotification(type, message) {
    const notification = document.getElementById('notification');
    notification.textContent = message;

    // Set the notification's class based on the type (success or error)
    notification.className = 'popup-message ' + type;

    // Show the notification
    notification.style.display = 'block';

    // Hide the notification after 3 seconds
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}
