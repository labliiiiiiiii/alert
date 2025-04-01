<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Modal</title>
    <style>
        /* Ensure the modal covers the entire viewport */
        .ADDresident-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            font-family: 'Poppins', sans-serif;
        }

        /* Modal content styling */
        .ADDresident-modal-content {
            background-color: #FCFAFB;
            margin: 5% auto;
            border-radius: 15px;
            width: 40%;
            max-height: 70vh;
            overflow: hidden;
            position: relative;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Modal body styling */
        .ADDresident-modal-body {
            background-color: #FCFAFB;
            padding: 10px 30px;
            flex-grow: 1;
            overflow-y: auto;
            box-sizing: border-box;
        }

        /* Prevent background scrolling when modal is open */
        body.modal-open {
            overflow: hidden;
        }

        /* Modal header styling */
        .ADDresident-modal-header {
            position: sticky;
            top: 0px;
            background-color: #FCFAFB;
            z-index: 100;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            margin-top: 0px;
            margin-bottom: 0px;
            padding-bottom: 0;
            border-bottom: 2px solid #ccc;
            margin-left: 20px;
            margin-right: 20px;
            margin-bottom: 20px;
        }

        /* Modal header title styling */
        .ADDresident-modal-header h2 {
            font-size: 20px;
            color: #333;
            font-weight: bold;
            text-align: left;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        /* Modal close button styling */
        .ADDresident-modal-close {
            position: absolute;
            top: 15px;
            right: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }

        /* Modal close button image styling */
        .ADDresident-modal-close img {
            width: 24px;
            height: 24px;
        }

        /* Modal close button hover effect */
        .ADDresident-modal-close:hover {
            background-color: rgba(31, 31, 41, 0.15);
        }

        /* Form label styling inside the modal */
        #addSingleModal form label {
            display: block;
            margin: 10px 0 5px;
            font-size: 0.80em;
            font-weight: 600;
            color: #333;
        }

        /* Form input, select, and textarea styling inside the modal */
        #addSingleModal form input,
        #addSingleModal form select,
        #addSingleModal form textarea {
            width: 100%;
            padding: 10px;
            margin: 0px 0 5px;
            border: 2px solid rgba(31, 31, 41, 0.15);
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.75em;
            font-weight: 400;
            box-sizing: border-box;
            background-color: #FCFAFB;
        }

        /* Ensure input fields are non-interactive in "View" mode */
        .ADDresident-modal-body input,
        .ADDresident-modal-body select,
        .ADDresident-modal-body textarea {
            pointer-events: none;
            background-color: #e9ecef;
            border: 2px solid #ddd;
            color: #1F1F29;
        }

        /* Modal footer styling */
        .ADDresident-modal-footer {
            background-color: #FCFAFB;
            padding: 10px 20px;
            text-align: center;
            border-top: 2px solid #ccc;
            position: sticky;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 50px;
            margin-left: 20px;
            margin-right: 20px;
            margin-top: 20px;
        }

        /* Modal footer button styling */
        .ADDresident-modal-footer button {
            font-family: 'Poppins', sans-serif;
            font-size: 0.75em;
            font-weight: 600;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
            margin-top: 5px;
            background-color: #2B3467;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Trigger Button -->
    <button class="add-single-btn">
        Open Add Resident Modal
    </button>

    <!-- Add Single Modal -->
    <div id="addSingleModal" class="ADDresident-modal">
        <div class="ADDresident-modal-content">
            <div class="ADDresident-modal-header">
                <span class="ADDresident-modal-close" onclick="closeModal()">
                    <img src="../img/plus/closeD.png" alt="Close">
                </span>
                <h2 id="addModalTitle">Add Resident Information</h2>
            </div>
            <div class="ADDresident-modal-body">
                <form action="../server/add_resident.php" id="addForm" enctype="multipart/form-data">
                    <label for="addFullname">Full Name:</label>
                    <input type="text" id="addFullname" name="fullname" required placeholder="Enter full name">

                    <label for="addSex">Sex:</label>
                    <select id="addSex" name="sex" required>
                        <option value="" disabled selected>Select Sex</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>

                    <label for="addBirthdate">Birthdate:</label>
                    <input type="date" id="addBirthdate" name="birthdate" required>

                    <label for="addAge">Age:</label>
                    <input type="number" id="addAge" name="age" required placeholder="Enter age">

                    <label for="addContact">Contact Number:</label>
                    <input type="text" id="addContact" name="contact" placeholder="Enter contact number">

                    <label for="addProvince">Province:</label>
                    <input type="text" id="addProvince" name="province" placeholder="Enter province">

                    <label for="addMunicipal">Municipal:</label>
                    <input type="text" id="addMunicipal" name="municipal" placeholder="Enter municipal">

                    <label for="addBarangay">Barangay:</label>
                    <input type="text" id="addBarangay" name="barangay" placeholder="Enter barangay">
                    <input type="hidden" name="barangay_id" value="">

                    <label for="addAddress">Address:</label>
                    <textarea id="addAddress" name="address" placeholder="Enter address"></textarea>
                </form>
            </div>

            <div class="ADDresident-modal-footer">
                <div class="left-aligned">
                    <span class="no-style" style="display: none;">or</span>
                    <a href="javascript:void(0);" class="add-multiple-text" onclick="addResidentsAtOnce()" style="display: none;">Add Multiple Residents at once</a>
                </div>
                <div class="right-aligned">
                    <button type="button" class="save-btn" onclick="saveResident()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addSingleBtn = document.querySelector('.add-single-btn');
            const modal = document.getElementById('addSingleModal');

            // Show the modal when the button is clicked
            if (addSingleBtn) {
                addSingleBtn.addEventListener('click', function() {
                    modal.style.display = 'block'; // Show the modal
                    document.body.classList.add('modal-open'); // Add modal-open class to body to prevent scroll
                });
            }

            // Close the modal when the close button is clicked
            const closeModal = document.querySelector('.ADDresident-modal-close');
            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    modal.style.display = 'none'; // Hide the modal
                    document.body.classList.remove('modal-open'); // Remove modal-open class from body
                });
            }

            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            });
        });

        // Close the modal function
        function closeModal() {
            const modal = document.getElementById('addSingleModal');
            modal.style.display = 'none'; // Hide the modal
            document.body.classList.remove('modal-open'); // Remove modal-open class from body
        }

        // Placeholder for additional modal functionality
        function saveResident() {
            alert('Form Submitted!');
        }

        function addResidentsAtOnce() {
            alert('Add Multiple Residents Functionality');
        }
    </script>
</body>
</html>
