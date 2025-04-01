let waterChart, humidityChart, tempChart;

document.addEventListener("DOMContentLoaded", async function () {
    if (document.getElementById("barangay") && "<?php echo $_SESSION['usertype']; ?>" === "admin") {
        await fetchBarangays();
    }
    fetchData(); // Automatically Load Data
});

async function fetchBarangays() {
    let response = await fetch('../server/fetch_alerts.php');
    let barangays = await response.json();
    let barangaySelect = document.getElementById("barangay");

    barangays.forEach(brgy => {
        let option = document.createElement("option");
        option.value = brgy;
        option.text = brgy;
        barangaySelect.appendChild(option);
    });
}

async function fetchData() {
    let formData = new FormData(document.getElementById("filterForm"));
    let response = await fetch("../server/fetch_data.php", {
        method: "POST",
        body: formData
    });
    let data = await response.json();

    // Destroy existing charts before creating new ones
    if (waterChart) waterChart.destroy();
    if (humidityChart) humidityChart.destroy();
    if (tempChart) tempChart.destroy();

    generateCharts(data);
}

function generateCharts(data) {
    let labels = [...new Set(data.map(alert => alert.dateTime))];
    let waterLevelData = data.map(alert => alert.waterLevel);
    let humidityData = data.map(alert => alert.humidity);
    let temperatureData = data.map(alert => alert.temperature);

    // Water Level Chart (Area Chart)
    waterChart = new Chart(document.getElementById("waterChart"), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Water Level',
                data: waterLevelData,
                borderColor: '#2B3467', // HEX color for the line (blue)
                backgroundColor: '#2B346766', // RGBA for the area under the line (light blue)
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#2B3467', // HEX color for the legend text (Tomato)
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '600' // Set font weight (bold)
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#1F1F29', // HEX color for X-axis text (Green)
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '500' // Set font weight (bold)
                        }
                    }
                },
                y: {
                    ticks: {
                        color: '#1F1F29', // HEX color for Y-axis text (Purple)
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '500' // Set font weight (bold)
                        }
                    }
                }
            },
            elements: {
                point: {
                    backgroundColor: '#FF0000' // HEX color for data points (Red)
                }
            }
        }
    });


    // Humidity Chart (Bar Chart)
    humidityChart = new Chart(document.getElementById("humidityChart"), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Humidity',
                data: humidityData,
                backgroundColor: '#5F8B4C', // HEX color for bars
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#5F8B4CB3', // HEX color for the legend text
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '600' // Set font weight (bold)
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#1F1F29', // HEX color for X-axis text
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '500' // Set font weight (regular)
                        }
                    }
                },
                y: {
                    ticks: {
                        color: '#1F1F29', // HEX color for Y-axis text
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '500' // Set font weight (regular)
                        }
                    }
                }
            }
        }
    });


    // Temperature Chart (Line Chart)
    tempChart = new Chart(document.getElementById("tempChart"), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Temperature',
                data: temperatureData,
                borderColor: '#A31D1D', // HEX color for the line (Blue)
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#A31D1DB3', // HEX color for the legend text
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '600' // Set font weight (bold)
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#1F1F29', // HEX color for X-axis text (Dark Gray)
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '500' // Set font weight (regular)
                        }
                    }
                },
                y: {
                    ticks: {
                        color: '#1F1F29', // HEX color for Y-axis text (Dark Gray)
                        font: {
                            family: 'Poppins, sans-serif', // Set font family
                            weight: '500' // Set font weight (regular)
                        }
                    }
                }
            }
        }
    });

}

// Reset Function for Filters
function resetFilters() {
    // Reset the form inputs
    document.getElementById("filterForm").reset();

    // Destroy existing charts
    if (waterChart) waterChart.destroy();
    if (humidityChart) humidityChart.destroy();
    if (tempChart) tempChart.destroy();

    // Optionally, you can call fetchData to reload data with default filters
    fetchData();
}
