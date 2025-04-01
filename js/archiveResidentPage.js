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

            fetch(`../server/fetch_resident_by_id_archive.php?id=${residentId}`)
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


function toggleDropdownPE() {
    document.getElementById('peAccountDropdown').style.display = 'block';
}




function populateModal(data) {
    const viewForm = document.getElementById('viewForm');

    if (!viewForm) {
        console.error('View form not found');
        return;
    }

    // Check if each field exists before setting its value
    if (viewForm.fullname) viewForm.fullname.value = data.fullname || 'No Fullname Found';
    if (viewForm.sex) viewForm.sex.value = data.sex || 'No Sex Found';
    if (viewForm.birthdate) viewForm.birthdate.value = data.birthdate || 'No Birthdate Found';
    if (viewForm.age) viewForm.age.value = data.age || 'No Age Found';
    if (viewForm.contact) viewForm.contact.value = data.contact || 'No Contact Found';
    if (viewForm.province) viewForm.province.value = data.province || 'No Province Found';
    if (viewForm.municipal) viewForm.municipal.value = data.municipal || 'No Municipal Found';
    if (viewForm.barangay) viewForm.barangay.value = data.BrgyName || 'No Barangay Found';
    if (viewForm.address) viewForm.address.value = data.address || 'No Address Found';
    if (viewForm.archivedat) viewForm.archivedat.value = data.archived_at || 'No Archived Date Found';
    if (viewForm.resident_id) viewForm.resident_id.value = data.residentid;

    // Make fields read-only for viewing
    viewForm.querySelectorAll('input, textarea').forEach(field => {
        field.setAttribute('readonly', true);
    });
}




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



    function exportToPDF() {
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;

        // Create a new PDF instance
        const doc = new jsPDF();

        // Set title and custom text
        doc.setFontSize(8);
        doc.setFont("helvetica", "normal");
        doc.text("Republic of the Philippines", 105, 20, { align: "center" }); // Reduced margin for this text

        doc.setFontSize(12);
        doc.setFont("helvetica", "bold");
        doc.text("MUNICIPAL OF CAINTA", 105, 25, { align: "center" }); // Reduced margin for this text

        doc.setFontSize(8);
        doc.setFont("helvetica", "normal");
        doc.text("Province of Rizal", 105, 28, { align: "center" }); // Reduced margin for this text

        doc.setFontSize(10);
        doc.setFont("helvetica", "normal");
        doc.text("i-Alert: Flood Monitoring and Alert System", 105, 40, { align: "center" }); // Reduced margin for this text

        // Get the table element by ID
        const table = document.getElementById("mainTable");

        // Convert the table to an array of data
        const rows = [];
        const headers = [];
        const tableHeaders = table.querySelectorAll("thead th");

        // Filter out unwanted <th> (checkbox, empty cells, and "Contact Number")
        tableHeaders.forEach((header) => {
            if (header.querySelector('input[type="checkbox"]') || header.innerText.trim() === "" || header.innerText === "Contact Number") {
                return; // Skip checkbox, empty headers, and "Contact Number"
            }
            headers.push(header.innerText);
        });

        rows.push(headers);

        // Get table data from the table body
        const tableRows = table.querySelectorAll("tbody tr");
        tableRows.forEach((row) => {
            // Skip the "No records found" row
            if (row.id === "no-records-row") return;

            const rowData = [];
            const cells = row.querySelectorAll("td");

            // Add data from each cell, excluding the checkbox, "Contact Number", and empty cells
            cells.forEach((cell, index) => {
                const headerText = tableHeaders[index].innerText; // Get the corresponding header text
                // Skip the first cell (checkbox), the "Contact Number" column, and the last empty cell
                if (index === 0 || headerText === "Contact Number" || index === cells.length - 1) return;
                rowData.push(cell.innerText);
            });

            rows.push(rowData);
        });

        // Use jsPDF's autotable plugin to add the table data with styling
        doc.autoTable({
            head: [headers],
            body: rows.slice(1), // Remove header from the body data
            theme: 'striped', // Adds striped styling to the table
            headStyles: {
                fillColor: [43, 52, 103], // Dark blue background for the header
                textColor: [255, 255, 255], // White text in header
                fontStyle: 'bold',
                fontSize: 8,
                halign: 'center' // Center align text
            },
            bodyStyles: {
                fontSize: 7, // Font size for the table body
                halign: 'center', // Center align text
            },
            margin: { top: 50, left: 5, right: 5, bottom: 5 }, // Adjusted margin for space at the top
            styles: {
                cellPadding: 1.5, // Padding for cells
                lineWidth: 0.5, // Border width
                lineColor: [0, 0, 0], // Border color (black)
                font: "helvetica" // Font used for the text
            }
        });

        // Save the generated PDF with a custom filename
        doc.save('resident_table.pdf');
    }
