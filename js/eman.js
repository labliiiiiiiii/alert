document.addEventListener('DOMContentLoaded', (event) => {
    // Fetch and display guidebooks for "All Manual" when the page loads
    fetchAndDisplayGuidebooks('all_manual');

    // Add the active class to the "All Manual" button
    const allManualButton = document.querySelector('.category-item[data-category-id="all_manual"]');
    if (allManualButton) {
        allManualButton.classList.add('active');
    }

    // Select the titleE element
    const titleElement = document.querySelector('.titleE');

    // Fetch categories from the server
    fetch('../server/fetch_emanCategories.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Populate the sidebar buttons
            const categoryList = document.querySelector('.category-list');
            if (categoryList) {
                categoryList.innerHTML = ''; // Clear existing buttons

                // Hardcode the "All Manual" button
                const allManualButton = document.createElement('button');
                allManualButton.className = 'category-item active'; // Add active class
                allManualButton.textContent = 'All Manual';
                allManualButton.dataset.categoryId = 'all_manual'; // Optional identifier
                allManualButton.onclick = () => {
                    // Remove active class from all category items
                    document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
                    // Add active class to the clicked category item
                    allManualButton.classList.add('active');
                    fetchAndDisplayGuidebooks('all_manual');
                    // Update the title with the category name
                    titleElement.textContent = allManualButton.textContent;
                };
                categoryList.appendChild(allManualButton);

                // Append dynamically fetched categories
                if (data.message) {
                    categoryList.innerHTML += '<p>No categories found</p>';
                } else {
                    data.forEach((category) => {
                        const button = document.createElement('button');
                        button.className = 'category-item';
                        button.textContent = category.name;
                        button.dataset.categoryId = category.categoryid;
                        button.onclick = () => {
                            // Remove active class from all category items
                            document.querySelectorAll('.category-item').forEach(item => item.classList.remove('active'));
                            // Add active class to the clicked category item
                            button.classList.add('active');
                            fetchAndDisplayGuidebooks(category.categoryid);
                            // Update the title with the category name
                            titleElement.textContent = button.textContent;
                        };
                        categoryList.appendChild(button);
                    });
                }
            }

            // Populate the manualCategory select element
            const manualCategorySelect = document.getElementById('manualCategory');
            if (manualCategorySelect) {
                manualCategorySelect.innerHTML = '<option value="" disabled selected>Select Category...</option>';
                if (data.message) {
                    manualCategorySelect.innerHTML += '<option value="" disabled>No categories found</option>';
                } else if (data.error) {
                    manualCategorySelect.innerHTML += '<option value="" disabled>Error fetching categories</option>';
                } else {
                    data.forEach((category) => {
                        const option = document.createElement('option');
                        option.value = category.categoryid;
                        option.textContent = category.name;
                        manualCategorySelect.appendChild(option);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
        });
});

// Function to fetch and display guidebooks based on category
function fetchAndDisplayGuidebooks(categoryId) {
    const url = categoryId === 'all_manual' ? '../server/fetch_emanBook.php' : `../server/fetch_emanBook.php?category=${categoryId}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const guidebookContainer = document.querySelector('.announcementBook');
            if (guidebookContainer) {
                guidebookContainer.innerHTML = ""; // Clear existing content

                if (data.success) {
                    data.guidebooks.forEach(guidebook => {
                        const guidebookElement = document.createElement("div");
                        guidebookElement.classList.add("guidebook-container");

                        // Build the content for each guidebook
                        guidebookElement.innerHTML = `
                            <div class="guidebook-text">
                                <div class="guidebook-title">${guidebook.title}</div>
                                <div class="guidebook-subtitle">${guidebook.category_name || "Uncategorized"}</div>
                            </div>
                            <!-- Dropdown Icon -->
                            <img src="../img/drop.png" alt="Dropdown Icon" class="dropdown-icon" data-filepath="${guidebook.filepath}">
                            <div class="file-preview" style="display: none;">
                                <embed src="" class="pdf-embed" type="application/pdf" style="width: 100%; height: 300px; display: none;">
                                <button id="open-pdf-button">Open PDF in New Tab</button>
                            </div>
                        `;
                        guidebookContainer.appendChild(guidebookElement);
                    });
                } else {
                    guidebookContainer.innerHTML = `<p>No guidebooks found</p>`;
                }
            }
        })
        .catch(error => console.error('Error fetching guidebooks:', error));
}

// The rest of your JavaScript code remains unchanged...
// Inject the button dynamically for this page
const systemSettings = document.querySelector(".system-settings");
if (systemSettings) {
    console.log("System settings element found");
    const button = document.createElement("button");
    button.className = "action-button";
    button.innerText = "Add Manual";
    button.onclick = () => {
        const modalNEW = document.getElementById("addManualModal");
        if (modalNEW) {
            console.log("Opening modal");
            modalNEW.style.display = "flex"; // Show the modal
            modalNEW.classList.add("visible"); // Make the modal visible
            document.body.classList.add("modal-open"); // Prevent background interaction
        } else {
            console.log("Modal element not found");
        }
    };
    systemSettings.appendChild(button);
} else {
    console.log("System settings element not found");
}

// Get the modal and button elements
const modal = document.getElementById("addCategoryModal");
const addNewBtn = document.querySelector(".add-new-btn");
const closeBtnE = document.querySelector(".close-btnE"); // Updated class name
const categoryForm = document.getElementById("categoryForm");

// Open the modal when the "Add New" button is clicked
if (addNewBtn) {
    addNewBtn.onclick = () => {
        modal.classList.add("show"); // Add the 'show' class to display the modal
        document.body.classList.add("modal-open"); // Prevent background interaction
    };
} else {
    console.log("Add New button not found");
}

// Close the modal when the close button is clicked
if (closeBtnE) {
    closeBtnE.onclick = () => {
        modal.classList.remove("show"); // Remove the 'show' class to hide the modal
        document.body.classList.remove("modal-open"); // Enable background interaction
    };
} else {
    console.log("Close button not found");
}

// Handle form submission for adding a new category
if (categoryForm) {
    categoryForm.onsubmit = (event) => {
        event.preventDefault(); // Prevent the form from reloading the page

        const categoryName = document.getElementById("categoryName").value; // Get the category name

        // Send the category name to the server
        fetch('../server/save_emanCategories.php', {  // Adjust the PHP file path accordingly
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name: categoryName }) // Sending the category name to the server
        })
        .then(response => response.json())  // Expecting a JSON response
        .then(data => {
            if (data.success) {
                // Close the modal and refresh the category list
                modal.classList.remove("show");  // Hide the modal
                document.body.classList.remove("modal-open"); // Enable background interaction
                setTimeout(() => {
                    location.reload();  // Reload the page to show the new category
                }, 1000); // Add a delay of 1 second before reloading
            } else {
                // Show error message if saving fails
                showMessage('error', data.message || 'Failed to save category.');
            }
        })
        .catch(error => {
            console.error('Error saving category:', error);  // Handle any network errors
            showMessage('error', 'An error occurred while saving the category.');
        });
    };
} else {
    console.log("Category form not found");
}

// Handle double-click event to edit category name
// Handle double-click event to edit category name
// Handle double-click event to edit category name
document.querySelector('.category-list').addEventListener('dblclick', (event) => {
    const categoryButton = event.target.closest('.category-item');
    if (categoryButton && categoryButton.dataset.categoryId !== 'all_manual') {
        // Create an input field for editing
        const input = document.createElement('input');
        input.type = 'text';
        input.value = categoryButton.textContent.trim();
        input.classList.add('editing-input');

        // Replace the button text with the input field
        categoryButton.textContent = '';
        categoryButton.appendChild(input);
        input.focus();

        // Save the new category name when the input loses focus
        input.addEventListener('blur', () => {
            const newCategoryName = input.value.trim();
            if (newCategoryName) {
                checkCategoryExistenceAndUpdate(categoryButton.dataset.categoryId, newCategoryName, categoryButton);
            }
            categoryButton.classList.remove('editing'); // Remove the class to revert the UI state
        });

        // Save the new category name when Enter is pressed
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                input.blur();
            }
        });
    }
});

// Function to check if the category name already exists
function checkCategoryExistenceAndUpdate(categoryId, newCategoryName, categoryButton) {
    fetch('../server/check_category_exists.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: categoryId, name: newCategoryName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            showMessage('error', 'Category name already exists. Please choose a different name.');
        } else {
            // If category doesn't exist, proceed with the update
            editCategory(categoryId, newCategoryName, categoryButton);
        }
    })
    .catch(error => {
        console.error('Error checking category existence:', error);
        showMessage('error', 'An error occurred while checking if the category exists.');
    });
}

// Function to edit the category
function editCategory(categoryId, newCategoryName, categoryButton) {
    console.log('Editing category with ID:', categoryId); // Log the category ID before sending to the server

    fetch('../server/edit_emanCategories.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: categoryId, name: newCategoryName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Category edited successfully.');
            showMessage('success', 'Category edited successfully.');
            setTimeout(() => {
                location.reload(); // Reload the page to reflect the changes
            }, 1000); // Add a delay of 1 second before reloading
        } else {
            console.error('Failed to edit category:', data.message);
            showMessage('error', data.message || 'Failed to edit category.');
        }
    })
    .catch(error => {
        console.error('Error editing category:', error);
        showMessage('error', 'An error occurred while editing the category.');
    });
}





// Get the modal and close button elements
const modalNEW = document.getElementById("addManualModal");
const closeModalBtnNEW = document.getElementById("closeModalBtnNEW");

// Close the modal when the close button is clicked
if (closeModalBtnNEW) {
    closeModalBtnNEW.onclick = () => {
        const modalNEW = document.getElementById("addManualModal");
        if (modalNEW) {
            console.log("Closing modal");
            modalNEW.classList.remove("visible"); // Hide the modal
            modalNEW.style.display = "none"; // Ensure the modal is fully hidden
            document.body.classList.remove("modal-open"); // Enable background interaction
        } else {
            console.log("Modal element not found");
        }
    };
} else {
    console.log("Close button for new manual modal not found");
}

// Handle file input change event for PDF preview
document.getElementById('fileUpload').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('pdfPreview');
    const pdfEmbed = document.getElementById('pdfEmbed');
    const noFileText = previewContainer.querySelector('p');

    // Check if the selected file is a PDF
    if (file && file.type === 'application/pdf') {
        // Show the preview and hide the "No file selected" text
        noFileText.style.display = 'none';
        pdfEmbed.style.display = 'block';

        // Set the PDF source to the selected file
        const fileURL = URL.createObjectURL(file);
        pdfEmbed.src = fileURL;
    } else {
        // Reset the preview if the file is not a PDF
        noFileText.style.display = 'block';
        pdfEmbed.style.display = 'none';
        showMessage('error', 'Please select a valid PDF file.');
    }
});

// Handle the upload button click event
document.getElementById('uploadManualBtn').onclick = function() {
    // Get the form elements
    const form = document.getElementById('manualForm');
    const manualTitle = document.getElementById('manualTitle').value;
    const manualCategory = document.getElementById('manualCategory').value;
    const fileUpload = document.getElementById('fileUpload').files[0]; // Get the first selected file

    // Check if a file is selected
    if (!fileUpload || fileUpload.type !== 'application/pdf') {
        showMessage('error', 'Please select a valid PDF file to upload.');
        return;
    }

    // Check if the title is unique
    fetch('../server/check_emanTitle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ title: manualTitle })
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            showMessage('error', 'A guidebook with this title already exists.');
        } else {
            // Show the confirmation popup
            const popupOverlay = document.getElementById("popupOverlay");
            popupOverlay.style.display = "flex";
            document.body.classList.add("modal-open"); // Prevent background interaction

            // Proceed with form submission if confirmed
            document.getElementById('proceedBtn').onclick = function() {
                // Create a new FormData object
                const formData = new FormData();
                formData.append('manualTitle', manualTitle);
                formData.append('manualCategory', manualCategory);
                formData.append('fileUpload', fileUpload);

                // Send the form data to the server using fetch
                fetch('../server/upload_emanCategories.php', {
                    method: 'POST',
                    body: formData // This sends the form data with the file
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Server response:', data); // Log the server response
                    if (data.success) {
                        // Close the modal
                        const modalNEW = document.getElementById("addManualModal");
                        if (modalNEW) {
                            modalNEW.classList.remove("visible"); // Hide the modal
                            modalNEW.style.display = "none"; // Ensure the modal is fully hidden
                        }
                        document.body.classList.remove("modal-open"); // Enable background interaction

                        // Show success message
                        showMessage('success', 'Manual uploaded successfully.');
                        setTimeout(() => {
                            location.reload(); // Reload the page or update UI accordingly
                        }, 1000); // Add a delay of 1 second before reloading
                    } else {
                        showMessage('error', data.message || 'File upload failed');
                    }
                })
                .catch(error => {
                    console.error('Error uploading file:', error);
                    showMessage('error', 'An error occurred while uploading the file.');
                });

                // Hide the confirmation popup
                popupOverlay.style.display = "none";
                document.body.classList.remove("modal-open"); // Enable background interaction
            };

            // Cancel the confirmation popup
            document.getElementById('cancelPopupBtn').onclick = function() {
                popupOverlay.style.display = "none";
                document.body.classList.remove("modal-open"); // Enable background interaction
            };
        }
    })
    .catch(error => {
        console.error('Error checking title:', error);
        showMessage('error', 'An error occurred while checking the title.');
    });
};

// Event listener for all dropdown icons
document.querySelector('.announcementBook').addEventListener('click', function(event) {
    if (event.target.classList.contains('dropdown-icon')) {
        const guidebookElement = event.target.closest('.guidebook-container');
        const filePreview = guidebookElement.querySelector('.file-preview');
        const pdfEmbed = guidebookElement.querySelector('.pdf-embed');
        const dropdownIcon = event.target;
        const openPdfButton = guidebookElement.querySelector('#open-pdf-button');

        // Toggle the file preview visibility
        if (filePreview.style.display === 'none') {
            const filePath = event.target.getAttribute('data-filepath'); // Get the file path from the data attribute
            pdfEmbed.src = filePath; // Set the PDF source to the file path
            filePreview.style.display = 'flex'; // Show the file preview
            pdfEmbed.style.display = 'block'; // Show the PDF embed
            dropdownIcon.classList.add('rotated'); // Rotate the icon
            openPdfButton.onclick = function() {
                window.open(filePath, '_blank');
            };
        } else {
            filePreview.style.display = 'none'; // Hide the file preview
            pdfEmbed.style.display = 'none'; // Hide the PDF embed
            dropdownIcon.classList.remove('rotated'); // Remove the rotation
        }
    }
});


document.getElementById('open-pdf-button').addEventListener('click', function() {
    const pdfEmbed = document.getElementById('pdf-embed');
    const pdfSrc = pdfEmbed.src;
    if (pdfSrc) {
        window.open(pdfSrc, '_blank');
    }
});


