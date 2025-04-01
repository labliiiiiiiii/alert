document.addEventListener('DOMContentLoaded', function() {
    // Initialize event listeners for barangay buttons
    const buttons = document.querySelectorAll('.loobButton');
    buttons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const barangayId = this.getAttribute('data-barangay-id');
            const barangayName = this.getAttribute('data-barangay-name');

            // Update active button state
            buttons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Fetch data with AJAX
            fetchData(barangayId, 1); // Fetch first page of the selected barangay

            // Log the barangay name and ID
            console.log('Barangay Name (from clicked button):', barangayName);
            console.log('Barangay ID (from clicked button):', barangayId);
        });
    });

    // Read the active barangay ID from the URL and set the active barangay
    const urlParams = new URLSearchParams(window.location.search);
    const activeBarangayId = urlParams.get('barangay');
    const currentPage = parseInt(urlParams.get('page')) || 1;
    if (activeBarangayId) {
        const activeButton = document.querySelector(`.loobButton[data-barangay-id="${activeBarangayId}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
            const barangayName = activeButton.getAttribute('data-barangay-name');
            fetchData(activeBarangayId, currentPage);
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
            fetchData(barangayId, 1);
        }
    }

    // Initialize event listeners for pagination
    document.querySelector('.pagination').addEventListener('click', function(event) {
        if (event.target.tagName === 'A') {
            event.preventDefault();
            const page = parseInt(event.target.textContent) || parseInt(event.target.getAttribute('data-page'));
            const activeButton = document.querySelector('.loobButton.active');
            const barangayId = activeButton.getAttribute('data-barangay-id');
            fetchData(barangayId, page);
        }
    });

    // Initialize event listeners for entries dropdown
    document.getElementById('entries').addEventListener('change', function() {
        const activeButton = document.querySelector('.loobButton.active');
        const barangayId = activeButton ? activeButton.getAttribute('data-barangay-id') : null;
        const entriesPerPage = this.value;
        const currentPage = parseInt(urlParams.get('page')) || 1;
        fetchData(barangayId, currentPage, entriesPerPage);
    });

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
    const peAccountBtn = document.querySelector('.print-account-btn');
    if (peAccountBtn) {
        peAccountBtn.addEventListener('mouseenter', toggleDropdownPE);
        peAccountBtn.addEventListener('mouseleave', function() {
            document.getElementById('peAccountDropdown').style.display = 'none';
        });
    }
});

function fetchData(barangayId, page, entriesPerPage = 5) {
    const search = document.getElementById('searchInput').value;

    const url = new URL(window.location.href);
    url.searchParams.set('barangay', barangayId);
    url.searchParams.set('page', page);
    url.searchParams.set('entries', entriesPerPage);
    url.searchParams.set('search', search);

    fetch(url.pathname + url.search)
        .then(response => response.text())
        .then(data => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const newTableBody = doc.querySelector('#mainTable tbody');
            const newPagination = doc.querySelector('.pagination');
            const newCount = doc.querySelector('.count');

            document.querySelector('#mainTable tbody').innerHTML = newTableBody.innerHTML;
            document.querySelector('.pagination').innerHTML = newPagination.innerHTML;
            document.querySelector('.count').innerHTML = newCount.innerHTML;

            // Update URL without reloading the page
            history.pushState({}, '', url.pathname + url.search);

            // Maintain scroll position
            const currentScroll = window.scrollY;
            window.scrollTo(0, currentScroll);

            // Ensure the active button remains active
            const activeButton = document.querySelector(`.loobButton[data-barangay-id="${barangayId}"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

function toggleDropdownPE() {
    document.getElementById('peAccountDropdown').style.display = 'block';
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

function handleEntriesChange() {
    const entriesForm = document.getElementById('entriesForm');
    const urlParams = new URLSearchParams(window.location.search);
    
    // Add other query parameters (like page, search, barangay) if they exist
    const entries = document.getElementById('entries').value;
    urlParams.set('entries', entries);

    // Update the form action to preserve current parameters
    window.location.search = urlParams.toString();  // This reloads the page with updated entries
}
