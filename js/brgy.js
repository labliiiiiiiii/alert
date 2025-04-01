document.addEventListener('DOMContentLoaded', function() {
    // Initialize event listeners for barangay buttons
    const buttons = document.querySelectorAll('.loobButton');
    buttons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const barangayId = this.getAttribute('data-barangay-id');
            const barangayName = this.getAttribute('data-barangay-name');
            filterTableByBarangay(barangayId);

            // Update active button state
            buttons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Update URL with the active barangay ID
            updateURLWithBarangayId(barangayId);

            // Log the barangay name and ID
            console.log('Barangay Name (from clicked button):', barangayName);
            console.log('Barangay ID (from clicked button):', barangayId);
        });

        
    });

    // Read the active barangay ID from the URL and set the active barangay
    const urlParams = new URLSearchParams(window.location.search);
    const activeBarangayId = urlParams.get('barangay');
    if (activeBarangayId) {
        const activeButton = document.querySelector(`.loobButton[data-barangay-id="${activeBarangayId}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
            const barangayName = activeButton.getAttribute('data-barangay-name');
            filterTableByBarangay(activeBarangayId);
            console.log('Active Barangay Name from URL:', barangayName);
            console.log('Active Barangay ID from URL:', activeBarangayId);
        }
    } else {
        // If no active barangay ID is found in the URL, set the first button as active
        const firstButton = buttons[0]; // First barangay button
        if (firstButton) {
            firstButton.classList.add('active');
            const barangayId = firstButton.getAttribute('data-barangay-id');
            const barangayName = firstButton.getAttribute('data-barangay-name');
            console.log('Default Barangay Name:', barangayName);
            console.log('Default Barangay ID:', barangayId);

            // Automatically filter table by the default barangay
            filterTableByBarangay(barangayId);

            // Update URL with the default barangay ID
            updateURLWithBarangayId(barangayId);
        }
    }

    // Initialize event listeners for view buttons
    const viewButtons = document.querySelectorAll('.view-btn');
    const modal = document.getElementById('viewModal');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = button.closest('tr');
            const residentId = row.querySelector('input[name="resident_ids[]"]').value;

            fetch(`../server/fetch_resident_by_id.php?id=${residentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        populateModal(data);
                        modal.style.display = 'block';
                        document.body.classList.add('modal-open'); // Add modal-open class to body
                    }
                })
                .catch(error => console.error('Error fetching resident data:', error));
        });
    });

    // Close modal event listeners
    document.querySelector('.resident-modal-close').addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open'); // Remove modal-open class from body
        console.clear(); // Clear the console when the modal is closed
    });

    // Close modal when clicking outside of modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    });

    // Initialize event listeners for dropdown
    const addAccountBtn = document.querySelector('.add-account-btn');
    if (addAccountBtn) {
        addAccountBtn.addEventListener('mouseenter', toggleDropdown);
        addAccountBtn.addEventListener('mouseleave', function() {
            document.getElementById('addAccountDropdown').style.display = 'none';
        });

        // Icon hover effects
        addAccountBtn.addEventListener('mouseover', function() {
            addAccountBtn.querySelector('.icon').src = '../img/plus/plusH.png';
        });

        addAccountBtn.addEventListener('mouseout', function() {
            addAccountBtn.querySelector('.icon').src = '../img/plus/plusD.png';
        });
    }

    const addSingleBtn = document.querySelector('.add-single-btn');
    if (addSingleBtn) {
        addSingleBtn.addEventListener('mouseover', function() {
            addSingleBtn.querySelector('.icon').src = '../img/plus/sH.png';
        });

        addSingleBtn.addEventListener('mouseout', function() {
            addSingleBtn.querySelector('.icon').src = '../img/plus/sD.png';
        });

        // Open modal for adding a single resident
        addSingleBtn.addEventListener('click', function() {
            console.log('Add Single button clicked'); // Debugging statement
            const modal = document.getElementById('addSingleModal');
            if (modal) {
                modal.style.display = 'block'; // Show modal
                document.body.classList.add('modal-open'); // Prevent background scroll
                console.log('Add Single Modal displayed'); // Debugging statement

                // Get the active barangay button
                const activeButton = document.querySelector('.loobButton.active');
                const barangayName = activeButton ? activeButton.getAttribute('data-barangay-name') : '';
                const barangayId = activeButton ? activeButton.getAttribute('data-barangay-id') : '';

                // Populate the barangay textbox and hidden input in the modal
                const barangayInput = modal.querySelector('input[name="barangay"]');
                const barangayIdInput = modal.querySelector('input[name="barangay_id"]');
                if (barangayInput && barangayIdInput) {
                    barangayInput.value = barangayName;
                    barangayIdInput.value = barangayId;
                    console.log('Barangay Name set in modal:', barangayName); // Debugging statement
                    console.log('Barangay ID set in modal:', barangayId); // Debugging statement
                } else {
                    console.error('Barangay input not found in modal'); // Debugging statement
                }
            } else {
                console.error('Add Single Modal not found'); // Debugging statement
            }
        });
    } else {
        console.error('Add Single button not found'); // Debugging statement
    }

    const addMultipleBtn = document.querySelector('.add-multiple-btn');
    if (addMultipleBtn) {
        addMultipleBtn.addEventListener('mouseover', function() {
            addMultipleBtn.querySelector('.icon').src = '../img/plus/mH.png';
        });

        addMultipleBtn.addEventListener('mouseout', function() {
            addMultipleBtn.querySelector('.icon').src = '../img/plus/mD.png';
        });

        // Open modal for adding multiple residents
        addMultipleBtn.addEventListener('click', function() {
            const activeButton = document.querySelector('.loobButton.active');
            const barangayName = activeButton ? activeButton.getAttribute('data-barangay-name') : '';
            const barangayId = activeButton ? activeButton.getAttribute('data-barangay-id') : '';

            console.log('Barangay Name (from active section for multiple):', barangayName);
            console.log('Barangay ID (from active section for multiple):', barangayId);

            const addMultipleModal = document.getElementById('addMultipleModal');
            addMultipleModal.style.display = 'block';
            document.body.classList.add('modal-open');
            resetRowsToThree(barangayName, barangayId);
        });
    }

    // Close modal when clicking the close button
    document.querySelector('.addMultipleModal-close').addEventListener('click', function() {
        const addMultipleModal = document.getElementById('addMultipleModal');
        addMultipleModal.style.display = 'none';
        document.body.classList.remove('modal-open');
        console.clear();
    });

    // Initialize the save confirmation popup
    setupSaveConfirmation('addForm');

    // Attach the saveResident function to the proceed button in the popup
    document.getElementById('proceedBtn').addEventListener('click', saveResident);


    // Initialize event listeners for dropdown
    const peAccountBtn = document.querySelector('.print-account-btn');
    if (peAccountBtn) {
        peAccountBtn.addEventListener('mouseenter', toggleDropdownPE);
        peAccountBtn.addEventListener('mouseleave', function() {
            document.getElementById('peAccountDropdown').style.display = 'none';
        });

    }
});

// Function to filter table by barangay
function filterTableByBarangay(barangayId) {
    const table = document.querySelector('.table-container table');
    const rows = table.getElementsByTagName('tr');
    let anyVisible = false;

    for (let i = 1; i < rows.length; i++) {
        const barangayCell = rows[i].getElementsByTagName('td')[8];

        if (barangayCell) {
            const barangayIdInTable = barangayCell.getAttribute('data-barangay-id');

            if (barangayIdInTable == barangayId) {
                rows[i].style.display = '';
                anyVisible = true;
            } else {
                rows[i].style.display = 'none';
            }
        }
    }

    const noRecordsRow = document.getElementById('no-records-row');
    if (noRecordsRow) {
        noRecordsRow.style.display = anyVisible ? 'none' : '';
    }
}

// Function to toggle dropdown visibility
function toggleDropdown() {
    document.getElementById('addAccountDropdown').style.display = 'block';
}

function toggleDropdownPE() {
    document.getElementById('peAccountDropdown').style.display = 'block';
}



// Function to toggle "Select All" checkbox
function toggleSelectAll(selectAllId, tableSelector) {
    const selectAllCheckbox = document.getElementById(selectAllId);
    const checkboxes = document.querySelectorAll(`${tableSelector} tbody input[type="checkbox"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Function to populate modal with data (always view mode)
function populateModal(data) {
    const viewForm = document.getElementById('viewForm');

    viewForm.fullname.value = data.fullname || 'No Fullname Found';
    viewForm.sex.value = data.sex || 'No Sex Found';
    viewForm.birthdate.value = data.birthdate || 'No Birthdate Found';
    viewForm.age.value = data.age || 'No Age Found';
    viewForm.contact.value = data.contact || 'No Contact Found';
    viewForm.province.value = data.province || 'No Province Found';
    viewForm.municipal.value = data.municipal || 'No Municipal Found';
    viewForm.barangay.value = data.BrgyName || 'No Barangay Found';
    viewForm.address.value = data.address || 'No Address Found';
    viewForm.resident_id.value = data.residentid;

    // Make fields read-only for viewing
    viewForm.querySelectorAll('input, textarea').forEach(field => {
        field.setAttribute('readonly', true);
    });
}

// Function to create a new row with the barangay name and ID
function createRow(barangayName, barangayId) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="checkbox"></td>
      <td><input type="text" name="fullname-MUL[]" placeholder="Fullname"></td>
      <td>
        <select name="sex-MUL[]" required>
          <option value="" disabled selected>Sex</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
      </td>
      <td><input type="date" name="birthday-MUL[]"></td>
      <td><input type="number" name="age-MUL[]" placeholder="Age"></td>
      <td><input type="tel" name="contact-MUL[]" placeholder="Contact Number" ></td>
      <td><input type="text" name="province-MUL[]" placeholder="Province"></td>
      <td><input type="text" name="municipal-MUL[]" placeholder="Municipal"></td>
      <td><input type="text" name="barangay-MUL[]" placeholder="Barangay" value="${barangayName || 'No Barangay Found'}"></td>
      <td><input type="text" name="address-MUL[]" placeholder="Address"></td>
      <td><input type="hidden" name="barangay_id-MUL[]" value="${barangayId || ''}"></td>
    `;

    // Attach event listener for the birthday input field
    const birthdayInput = tr.querySelector('input[name="birthday-MUL[]"]');
    birthdayInput.addEventListener('change', function() {
        const birthdate = new Date(this.value);
        if (this.value && !isNaN(birthdate)) {
            const today = new Date();
            let age = today.getFullYear() - birthdate.getFullYear();
            const month = today.getMonth() - birthdate.getMonth();

            if (month < 0 || (month === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }

            const ageInput = this.closest('tr').querySelector('input[name="age-MUL[]"]');
            if (ageInput) {
                ageInput.value = age;
                ageInput.setAttribute('readonly', true);
                ageInput.style.userSelect = 'none';  // Prevent text selection
                ageInput.style.backgroundColor = '#e9ecef';  // Optional: Change the background to indicate it's readonly
                ageInput.style.pointerEvents = 'none';  // Prevent any interaction with the field
            }
        } else {
            const ageInput = this.closest('tr').querySelector('input[name="age-MUL[]"]');
            if (ageInput) {
                ageInput.value = '';
            }
        }
    });


    return tr;
}

// Function to add rows to the table with the barangay name and ID
function addRows(numRows, barangayName, barangayId) {
    const tbody = document.getElementById('dynamicTable').getElementsByTagName('tbody')[0];
    for (let i = 0; i < numRows; i++) {
        tbody.appendChild(createRow(barangayName, barangayId));
    }
}

// Function to reset the rows to 3 with the barangay name and ID
function resetRowsToThree(barangayName, barangayId) {
    const tbody = document.getElementById('dynamicTable').getElementsByTagName('tbody')[0];

    // Remove all existing rows
    while (tbody.rows.length > 0) {
        tbody.deleteRow(0);
    }

    // Add 3 new rows with the barangay name and ID
    addRows(3, barangayName, barangayId);
}

// Initial load to add 3 rows when the page is first loaded
const firstButton = document.querySelector('.loobButton.active');
const initialBarangayName = firstButton ? firstButton.getAttribute('data-barangay-name') : '';
const initialBarangayId = firstButton ? firstButton.getAttribute('data-barangay-id') : '';
addRows(3, initialBarangayName, initialBarangayId);

// Function to add rows when the button is clicked
function addRow() {
    const activeButton = document.querySelector('.loobButton.active');
    const barangayName = activeButton ? activeButton.getAttribute('data-barangay-name') : '';
    const barangayId = activeButton ? activeButton.getAttribute('data-barangay-id') : '';
    const tbody = document.getElementById('dynamicTable').getElementsByTagName('tbody')[0];
    tbody.appendChild(createRow(barangayName, barangayId));
}

// Event listener for the "Add Row" button
document.querySelector('.add-row-btn').addEventListener('click', function() {
    addRow(); // Call the addRow function when the button is clicked
});

// Function to remove selected rows from the table
function removeSelectedRows() {
    const tableBody = document.getElementById('dynamicTable').getElementsByTagName('tbody')[0];
    const checkboxes = tableBody.querySelectorAll('input[type="checkbox"]:checked');

    // Loop through the checkboxes and remove the corresponding rows
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        row.remove();
    });
}

// Event listener for the "Remove Row" button
document.querySelector('.remove-row-btn').addEventListener('click', function() {
    removeSelectedRows(); // Call the removeSelectedRows function when the button is clicked
});

// Function to compute age
document.getElementById('addBirthdate').addEventListener('change', function() {
    // Get the selected birthdate
    const birthdate = new Date(this.value);

    // Get the current date
    const today = new Date();

    // Calculate the age
    let age = today.getFullYear() - birthdate.getFullYear();
    const month = today.getMonth() - birthdate.getMonth();

    // Adjust age if current month and day are before the birthdate
    if (month < 0 || (month === 0 && today.getDate() < birthdate.getDate())) {
        age--;
    }

    // Set the calculated age in the 'age' input field
    const ageField = document.getElementById('addAge');
    ageField.value = age;

    // Disable the 'age' field to prevent manual editing
    ageField.setAttribute('readonly', true);  // Make the age input field readonly
    ageField.style.userSelect = 'none';  // Prevent text selection
    ageField.style.backgroundColor = '#e9ecef';  // Optional: Change the background to indicate it's readonly
    ageField.style.pointerEvents = 'none';  // Prevent any interaction with the field
});

// Define the function in the global scope
function openCSVModal() {
    // Close the current modal if it's open (check for any open modal)
    const currentModal = document.querySelector('.CSVmodal-container');
    const addMultipleModal = document.getElementById('addMultipleModal');
    const addSingleModal = document.getElementById('addSingleModal');

    if (currentModal && currentModal.style.display === 'block') {
        currentModal.style.display = 'none';  // Hide the CSV modal if it's open
        document.body.classList.remove('modal-open');  // Remove the body class
    }

    // Optionally, close any other modals that might be open
    if (addMultipleModal && addMultipleModal.style.display === 'block') {
        addMultipleModal.style.display = 'none';  // Close add multiple modal if it's open
        document.body.classList.remove('modal-open');
    }

    if (addSingleModal && addSingleModal.style.display === 'block') {
        addSingleModal.style.display = 'none';  // Close the view modal if it's open
        addSingleModal.body.classList.remove('modal-open');
    }

    // Open the "CSV Modal"
    const csvModal = document.getElementById('csvModal');
    if (csvModal) {
        csvModal.style.display = 'block';  // Show the modal
        document.body.classList.add('modal-open');  // Optional: Add modal-open class for styling
    }

    // Open the add multiple modal when the 'Back' button inside the CSV modal is clicked
    document.getElementById('backButton').addEventListener('click', function() {
        document.getElementById('csvModal').style.display = 'none';  // Close CSV modal
        document.getElementById('addMultipleModal').style.display = 'block';  // Open the Add Multiple modal
    });
}



// Function to open the "Add Multiple Modal" and close the "Add Single Modal"
function openAddMultipleModal() {
    const addSingleModal = document.getElementById('addSingleModal');
    const addMultipleModal = document.getElementById('addMultipleModal');

    if (addSingleModal && addMultipleModal) {
        addSingleModal.style.display = 'none'; // Close the "Add Single Modal"
        addMultipleModal.style.display = 'block'; // Open the "Add Multiple Modal"
        document.body.classList.add('modal-open'); // Prevent background scroll

        // Get the active barangay button
        const activeButton = document.querySelector('.loobButton.active');
        const barangayName = activeButton ? activeButton.getAttribute('data-barangay-name') : '';
        const barangayId = activeButton ? activeButton.getAttribute('data-barangay-id') : '';

        console.log('Barangay Name (from active section for multiple):', barangayName);
        console.log('Barangay ID (from active section for multiple):', barangayId);

        resetRowsToThree(barangayName, barangayId);
    } else {
        console.error('Modals not found'); // Debugging statement
    }
}

// Function to update the URL with the active barangay ID
function updateURLWithBarangayId(barangayId) {
    const url = new URL(window.location.href);
    url.searchParams.set('barangay', barangayId);
    window.history.replaceState({}, '', url.toString());
}

// Function to display the notification
function showNotification(type, message) {
    const notification = document.getElementById('notification');
    notification.className = 'notification ' + type;
    notification.innerText = message;
    notification.style.display = 'block';

    // Hide the notification after 3 seconds
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Function to check for session messages and display notifications
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

// Call displayNotification() on page load to check for messages
window.addEventListener('DOMContentLoaded', displayNotification);








document.addEventListener('DOMContentLoaded', function() {
    // Initialize the save confirmation popup
    const proceedBtn = document.getElementById("proceedBtnMAIN");
    const cancelPopupBtn = document.getElementById("cancelPopupBtnMAIN");
    const popupOverlay = document.getElementById("popupOverlayMAIN");

    if (proceedBtn && cancelPopupBtn && popupOverlay) {
        let saveType = '';

        // Show the confirmation popup
        window.showSaveConfirmation = function(type) {
            saveType = type;
            popupOverlay.style.display = "flex"; // Show the confirmation popup
        };

        // Remove any existing event listeners before adding a new one
        proceedBtn.removeEventListener("click", handleProceedClick);
        proceedBtn.addEventListener("click", handleProceedClick);

        function handleProceedClick() {
            if (saveType === 'single') {
                saveResident(); // Call the saveResident function
            } else if (saveType === 'multiple') {
                saveMultipleResidents(); // Call the saveMultipleResidents function
            } else if (saveType === 'csv') {
                uploadCSVFile(); // Call the uploadCSVFile function for CSV upload
            }
            popupOverlay.style.display = "none"; // Hide popup after saving
        }

        // Cancel the popup and prevent form submission
        cancelPopupBtn.addEventListener("click", () => {
            popupOverlay.style.display = "none"; // Hide the popup on cancel
        });

        // Optional: Function to close the overlay if clicked outside the popup
        popupOverlay.addEventListener("click", (e) => {
            if (e.target === popupOverlay) {
                popupOverlay.style.display = "none";
            }
        });
    } else {
        console.error('One or more required elements are missing.');
    }
});


// Function to handle saving single resident info
function saveResident() {
    // Get the input values
    const fullname = document.getElementById('addFullname').value;
    const sex = document.getElementById('addSex').value;
    const birthdate = document.getElementById('addBirthdate').value;
    const age = document.getElementById('addAge').value;
    const contact = document.getElementById('addContact').value;
    const province = document.getElementById('addProvince').value;
    const municipal = document.getElementById('addMunicipal').value;
    const barangay = document.getElementById('addBarangay').value;
    const address = document.getElementById('addAddress').value;

    // Ensure all required fields are filled
    if (!fullname || !sex || !birthdate || !age || !contact || !province || !municipal || !barangay || !address) {
        showNotification('error', 'Please fill in all the fields.');
        return; // Prevent submission if any field is missing
    }

    // Prepare data for the POST request
    const formData = new FormData();
    formData.append('fullname', fullname);
    formData.append('sex', sex);
    formData.append('birthdate', birthdate);
    formData.append('age', age);
    formData.append('contact', contact);
    formData.append('province', province);
    formData.append('municipal', municipal);
    formData.append('barangay_id', document.querySelector('input[name="barangay_id"]').value); // Hidden input value
    formData.append('address', address);

    // Send the form data using Fetch API to save the resident data
    fetch('../server/save_resident.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display success notification
            showNotification('success', data.message);
            // Close the modal, reset the form, and reload the page only on success
            document.getElementById('addSingleModal').style.display = 'none';
            window.location.reload(); // Reload the page or redirect to reflect the changes
        } else {
            // Display error notification without closing the modal
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'An error occurred while saving the resident information. Please try again.');
    });
}


function saveMultipleResidents() {
    const tableBody = document.getElementById('dynamicTable').getElementsByTagName('tbody')[0];
    const rows = tableBody.getElementsByTagName('tr');
    const formData = new FormData();
    const residents = [];

    // Loop through the rows and collect the data
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const fullname = row.querySelector('input[name="fullname-MUL[]"]').value;
        const sex = row.querySelector('select[name="sex-MUL[]"]').value;
        const birthday = row.querySelector('input[name="birthday-MUL[]"]').value;
        const age = row.querySelector('input[name="age-MUL[]"]').value;
        const contact = row.querySelector('input[name="contact-MUL[]"]').value;
        const province = row.querySelector('input[name="province-MUL[]"]').value;
        const municipal = row.querySelector('input[name="municipal-MUL[]"]').value;
        const barangay = row.querySelector('input[name="barangay-MUL[]"]').value;
        const barangayId = row.querySelector('input[name="barangay_id-MUL[]"]').value;
        const address = row.querySelector('input[name="address-MUL[]"]').value;

        // Ensure all required fields are filled
        if (!fullname || !sex || !birthday || !age || !contact || !province || !municipal || !barangay || !address) {
            showNotification('error', 'Please fill in all the fields for each row.');
            return; // Prevent submission if any field is missing
        }

        // Append the data to the residents array
        residents.push({
            fullname: fullname,
            sex: sex,
            birthday: birthday,
            age: age,
            contact: contact,
            province: province,
            municipal: municipal,
            barangay_id: barangayId,
            address: address
        });
    }

    // Append the residents array to the FormData object
    formData.append('residents', JSON.stringify(residents));

    // Send the form data using Fetch API to save the resident data
    fetch('../server/save_resident.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display success notification
            showNotification('success', data.message);
            // Close the modal, reset the form, and reload the page only on success
            document.getElementById('addMultipleModal').style.display = 'none';
            window.location.reload(); // Reload the page or redirect to reflect the changes
        } else {
            // Display error notification without closing the modal
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'An error occurred while saving the resident information. Please try again.');
    });
}

function uploadCSVFile() {
    var fileInput = document.getElementById('fileUpload');
    var file = fileInput.files[0];

    if (!file) {
        showNotification('error', "Please select a file to upload.");
        return;
    }

    // Get the barangay ID from the active barangay button
    const activeButton = document.querySelector('.loobButton.active');
    const barangayId = activeButton ? activeButton.getAttribute('data-barangay-id') : '';

    // Log the barangay ID to the console
    console.log('Barangay ID:', barangayId);  // This will show the barangay ID in the console

    // If no barangay ID is found, show an error
    if (!barangayId) {
        showNotification('error', "Please select a barangay.");
        return;
    }

    var formData = new FormData();
    formData.append('csvFile', file);
    formData.append('barangay_id', barangayId); // Append barangay ID to the form data

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../server/upload_csv.php', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Display success message from server response
            showNotification('success', xhr.responseText);
            console.log(xhr.responseText); // Log the response message for debugging
            document.getElementById('csvModal').style.display = 'none';
            window.location.reload(); // Reload the page or redirect to reflect the changes
        } else {
            showNotification('error', "Error uploading file.");
            console.error("Error uploading file:", xhr.responseText); // Log error in console if failed
        }
    };
    xhr.send(formData);
}



function updateFileName() {
    // Get the file input element
    var fileInput = document.getElementById('fileUpload');
    var fileName = fileInput.files[0] ? fileInput.files[0].name : "No file selected";

    // Get the label where we want to update the text
    var fileLabel = document.getElementById('fileLabel');

    // Change the label text to "Upload" and display the file name
    fileLabel.textContent = "Upload: " + fileName;
}


document.addEventListener('DOMContentLoaded', function() {
    // Initialize the archive confirmation popup
    const proceedBtn = document.getElementById("proceedBtnMAIN_ARCHIVE");
    const cancelPopupBtn = document.getElementById("cancelPopupBtnMAIN_ARCHIVE");
    const popupOverlay = document.getElementById("popupOverlayMAIN_ARCHIVE");
    const archiveBtn = document.getElementById("archiveBtn"); // Archive Resident button

    if (proceedBtn && cancelPopupBtn && popupOverlay) {
        let saveType = '';

        // Show the confirmation popup
        window.showArchiveConfirmation = function(type) {
            saveType = type;
            popupOverlay.style.display = "flex"; // Show the confirmation popup
        };

        // Remove any existing event listeners before adding a new one
        proceedBtn.removeEventListener("click", handleProceedClick);
        proceedBtn.addEventListener("click", handleProceedClick);

        function handleProceedClick() {
            if (saveType === 'multiple') {
                // Proceed to archive the selected residents
                archiveMultipleResidents(); // Call the archiveMultipleResidents function for archiving
            }
            popupOverlay.style.display = "none"; // Hide popup after saving
        }

        // Cancel the popup and prevent form submission
        cancelPopupBtn.addEventListener("click", () => {
            popupOverlay.style.display = "none"; // Hide the popup on cancel
        });

        // Optional: Function to close the overlay if clicked outside the popup
        popupOverlay.addEventListener("click", (e) => {
            if (e.target === popupOverlay) {
                popupOverlay.style.display = "none";
            }
        });
    } else {
        console.error('One or more required elements are missing.');
    }

    // Show the archive confirmation when the "Archive Resident" button is clicked
    archiveBtn.addEventListener('click', function() {
        // Show confirmation popup
        window.showArchiveConfirmation('multiple');
    });

    // Function to handle archiving selected residents
    function archiveMultipleResidents() {
        const selectedResidents = [];
        const checkboxes = document.querySelectorAll('#mainTable input[name="resident_ids[]"]:checked');
        
        checkboxes.forEach(function(checkbox) {
            selectedResidents.push(checkbox.value); // Get the value of each checkbox (resident ID)
        });

        if (selectedResidents.length > 0) {
            // Send the selected resident IDs to the server via AJAX for archiving
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../server/archive_Resident.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Parse the JSON response from the server
                    var response = JSON.parse(xhr.responseText);

                    // Check if the response indicates success
                    if (response.status === 'success') {
                        // Show success notification
                        showNotification('success', response.message);
                    } else {
                        // Show error notification
                        showNotification('error', response.message);
                    }

                   // Delay the page reload by 2 seconds (2000ms)
                    setTimeout(function() {
                        window.location.reload(); // Reload the page after a slight delay
                    }, 500); // Adjust the delay time here (in milliseconds)
                } else {
                    showNotification('error', 'Error archiving residents.');
                    setTimeout(function() {
                        window.location.reload(); // Reload the page after a slight delay
                    }, 500); // Adjust the delay time here (in milliseconds)
                }
            };

            // Prepare the data to send (the selected resident IDs)
            var data = 'residentids=' + encodeURIComponent(JSON.stringify(selectedResidents));

            // Send the data to the server
            xhr.send(data);
        } else {
            // Show error notification if no residents are selected
            showNotification('error', 'Please select at least one resident to archive.');
            setTimeout(function() {
                window.location.reload(); // Reload the page after a slight delay
            }, 500); // Adjust the delay time here (in milliseconds)
        }
    }


});



function printTablePRINT() {
    // Clone the header and table content for printing
    var printContent = document.getElementById("mainTablePRINT").outerHTML; // Get the table's outer HTML
    var headerContent = document.querySelector(".residentPRINT-header").outerHTML; // Get the header's outer HTML

    // Create a new hidden iframe for printing
    var iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0px';
    iframe.style.height = '0px';
    iframe.style.border = 'none';
    document.body.appendChild(iframe);

    var iframeDoc = iframe.contentWindow.document;

    // Wait for the iframe's document to be fully loaded before assigning content
    iframeDoc.open();
    iframeDoc.write('<html><head><title>Print</title><style>' +
        /* Your CSS for printing */
        "#printRES .residentPRINT-header, #printRES .tablePRINT-container { display: none; }" +
        "@media print { " +
        "#printRES .residentPRINT-header, #printRES .tablePRINT-container { display: block !important; }" +
        "body { font-family: 'Poppins', sans-serif !important; margin: 0 !important; padding: 10px !important; background-color: #fff !important; }" +
        "h2, h3 { font-size: 18px !important; margin: 10px 0 !important; }" +
        "p { font-size: 14px !important; margin: 5px 0 !important; }" +
        "table { width: 100% !important; border-collapse: collapse !important; margin-top: 20px !important; }" +
        "thead { background-color: #2B3467 !important; }" +
        "th, td { padding: 10px !important; text-align: left !important; font-size: 14px !important; }" +
        "th { background-color: #f2f2f2 !important; color: #333 !important; border: 1px solid #ddd !important; }" +
        "td { border: 1px solid #ddd !important; }" +
        "tr:nth-child(even) { background-color: #f9f9f9 !important; }" +
        "tr:hover { background-color: #f1f1f1 !important; }" +
        ".residentPRINT-header { margin-bottom: 30px !important; text-align: center !important; }" +
        ".residentPRINT-header p, .residentPRINT-header h2, .residentPRINT-header h3 { margin: 0 !important; padding: 0 !important; }" +
        ".residentPRINT-header h2 { font-size: 22px !important; font-weight: bold !important; }" +
        ".residentPRINT-header h3 { font-size: 20px !important; font-weight: normal !important; color: #555 !important; margin-top: 5px !important; }" +
        "#printRES .print-exclude { display: none !important; }" +
        "#printRES .PE-account-dropdown, #printRES .export-btn, #printRES .print-account-btn { display: none !important; }" +
        "}</style></head><body>");
    iframeDoc.close();

    // After closing the iframe document, add the content
    iframeDoc.body.innerHTML = '<div id="printRES">' + headerContent + printContent + '</div>';

    // Trigger print for the iframe content
    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    // Remove the iframe after printing to clean up
    document.body.removeChild(iframe);
}

function exportToCSV() {
    // Get all rows, regardless of pagination
    const allRows = document.querySelectorAll('#mainTable tbody tr');

    // Initialize an array to hold the CSV data
    let csvData = [];

    // Add the header row
    const headerRow = [];
    document.querySelectorAll('#mainTable thead th').forEach(th => {
        // Trim spaces from header text
        headerRow.push(th.innerText.trim());
    });
    csvData.push(headerRow);

    // Add the table data rows
    allRows.forEach(row => {
        const rowData = [];
        row.querySelectorAll('td').forEach(cell => {
            let cellText = cell.innerText.trim();  // Trim spaces
            // If cell contains commas, newlines, or quotes, wrap in quotes and escape quotes
            if (cellText.includes(',') || cellText.includes('\n') || cellText.includes('"')) {
                cellText = `"${cellText.replace(/"/g, '""')}"`;
            }
            rowData.push(cellText);
        });
        csvData.push(rowData);
    });

    // Create a CSV string, ensuring each row is joined by a comma and each row is separated by a newline
    const csvString = csvData.map(row => row.join(',')).join('\n');

    // Create a Blob from the CSV string
    const blob = new Blob([csvString], { type: 'text/csv' });

    // Create a temporary link element
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'i-Alert.csv';  // The name of the file to download

    // Append the link to the body, click it to start the download, then remove it
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
