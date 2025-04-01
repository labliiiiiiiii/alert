function printTablePRINT() {
    // Clone the header and table content for printing
    var printContent = document.getElementById("mainTablePRINT").outerHTML; // Get the table's outer HTML
    var headerContent = document.querySelector(".ActivityLog-header").outerHTML; // Get the header's outer HTML

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
        "#printRES .ActivityLog-header, #printRES .tablePRINT-container { display: none; }" +
        "@media print { " +
        "#printRES .ActivityLog-header, #printRES .tablePRINT-container { display: block !important; }" +
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
        ".ActivityLog-header { margin-bottom: 30px !important; text-align: center !important; }" +
        ".ActivityLog-header p, .ActivityLog-header h2, .ActivityLog-header h3 { margin: 0 !important; padding: 0 !important; }" +
        ".ActivityLog-header h2 { font-size: 22px !important; font-weight: bold !important; }" +
        ".ActivityLog-header h3 { font-size: 20px !important; font-weight: normal !important; color: #555 !important; margin-top: 5px !important; }" +
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
    // Ensure jsPDF is available
    if (typeof window.jspdf === "undefined") {
        alert("jsPDF library is not loaded. Please check the script source.");
        return;
    }

    // Load jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF("p", "mm", "a4");

    // Set font
    doc.setFont("helvetica");

    // Add report header
    doc.setFontSize(12);
    doc.text("Republic of the Philippines", 105, 15, { align: "center" });

    doc.setFontSize(18);
    doc.setFont("helvetica", "bold");
    doc.text("MUNICIPAL OF CAINTA", 105, 23, { align: "center" });

    doc.setFontSize(12);
    doc.setFont("helvetica", "normal");
    doc.text("Province of Rizal", 105, 30, { align: "center" });

    doc.setFontSize(14);
    doc.setFont("helvetica", "bold");
    doc.text("i-Alert: Flood Monitoring and Alert System", 105, 38, { align: "center" });

    doc.setFontSize(12);
    doc.text("ACTIVITY LOG", 105, 44, { align: "center" });

    // Get table headers
    const headers = [];
    document.querySelectorAll('.activity-log-table thead th').forEach(th => {
        headers.push(th.innerText.trim());
    });

    // Get table data
    const data = [];
    document.querySelectorAll('.activity-log-table tbody tr').forEach(row => {
        const rowData = [];
        row.querySelectorAll('td').forEach(cell => {
            rowData.push(cell.innerText.trim());
        });
        data.push(rowData);
    });

    // Check if there's data to export
    if (data.length === 0) {
        alert("No data to export!");
        return;
    }

    // Add table to PDF using autoTable
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 50, // Adjusted to fit the header properly
        theme: 'grid',
        styles: { fontSize: 10 },
        headStyles: { fillColor: [0, 0, 0], textColor: [255, 255, 255] }, // Black header with white text
        margin: { top: 20 },
    });

    // Save the PDF
    doc.save("Activity_Logs.pdf");
}



function toggleDropdownPE() {
        var dropdown = document.getElementById("peAccountDropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function(event) {
        var dropdown = document.getElementById("peAccountDropdown");
        var button = document.querySelector(".print-account-btn");

        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

  
    document.getElementById('filterForm').onsubmit = function () {
            
        };