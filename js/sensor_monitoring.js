document.addEventListener('DOMContentLoaded', function() {
    const sensorForm = document.getElementById('sensorForm');
    const sensorTable = document.getElementById('sensorTable').getElementsByTagName('tbody')[0];
    
    // Initialize charts
    const charts = initializeCharts();
    
    // Load existing sensor data and update charts
    loadSensorData();
    
    // Set up real-time updates every 30 seconds
    setInterval(loadSensorData, 30000);

    // Form submission handler
    sensorForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            sensorId: document.getElementById('sensorId').value,
            humidity: document.getElementById('humidity').value,
            oxygenLevel: document.getElementById('oxygenLevel').value,
            pH: document.getElementById('pH').value,
            temperature: document.getElementById('temperature').value,
            readingTimestamp: document.getElementById('readingTimestamp').value
        };

        // Send data to PHP backend
        fetch('php/save_sensor_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Sensor data saved successfully!');
                const newRow = sensorTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.sensorId}</td>
                        <td>${formData.humidity}</td>
                        <td>${formData.oxygenLevel}</td>
                        <td>${formData.pH}</td>
                        <td>${formData.temperature}</td>
                        <td>${formatTimestamp(formData.readingTimestamp)}</td>
                        <td>
                            <button onclick="editSensorData('${formData.sensorId}')">Edit</button>
                            <button onclick="deleteSensorData('${formData.sensorId}')">Delete</button>
                        </td>
                    `;
                sensorForm.reset();
                // Also update charts with the new datapoint
                updateCharts([
                    {
                        sensor_id: formData.sensorId,
                        humidity: Number(formData.humidity),
                        oxygen_level: Number(formData.oxygenLevel),
                        ph_level: Number(formData.pH),
                        temperature: Number(formData.temperature),
                        reading_timestamp: formData.readingTimestamp
                    }
                ], charts);
            } else {
                alert('Error saving sensor data: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving sensor data: ' + error.message);
        });
    });

    function initializeCharts() {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        // Use moment.js for more reliable date handling
                        tooltipFormat: 'YYYY-MM-DD HH:mm:ss',
                        displayFormats: {
                            millisecond: 'HH:mm:ss.SSS',
                            second: 'HH:mm:ss',
                            minute: 'HH:mm',
                            hour: 'MMM DD HH:mm',
                            day: 'MMM DD',
                            week: 'MMM DD',
                            month: 'MMM YYYY',
                            quarter: '[Q]Q - YYYY',
                            year: 'YYYY'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Value'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        };

        // Create charts with error handling and fallback
        let temperatureChart, humidityChart, oxygenChart, pHChart;
        
        try {
            temperatureChart = new Chart(document.getElementById('temperatureChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Temperature (°C)',
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        data: []
                    }]
                },
                options: commonOptions
            });
        } catch (error) {
            console.error('Error creating temperature chart:', error);
            // Fallback to simpler configuration
            temperatureChart = new Chart(document.getElementById('temperatureChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Temperature (°C)',
                        borderColor: 'rgb(255, 99, 132)',
                        data: []
                    }]
                },
                options: { responsive: true }
            });
        }

        try {
            humidityChart = new Chart(document.getElementById('humidityChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Humidity (%)',
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        data: []
                    }]
                },
                options: commonOptions
            });
        } catch (error) {
            console.error('Error creating humidity chart:', error);
            humidityChart = new Chart(document.getElementById('humidityChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Humidity (%)',
                        borderColor: 'rgb(54, 162, 235)',
                        data: []
                    }]
                },
                options: { responsive: true }
            });
        }

        try {
            oxygenChart = new Chart(document.getElementById('oxygenChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Oxygen Level (%)',
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        data: []
                    }]
                },
                options: commonOptions
            });
        } catch (error) {
            console.error('Error creating oxygen chart:', error);
            oxygenChart = new Chart(document.getElementById('oxygenChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Oxygen Level (%)',
                        borderColor: 'rgb(75, 192, 192)',
                        data: []
                    }]
                },
                options: { responsive: true }
            });
        }

        try {
            pHChart = new Chart(document.getElementById('pHChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'pH Level',
                        borderColor: 'rgb(153, 102, 255)',
                        backgroundColor: 'rgba(153, 102, 255, 0.1)',
                        data: []
                    }]
                },
                options: commonOptions
            });
        } catch (error) {
            console.error('Error creating pH chart:', error);
            pHChart = new Chart(document.getElementById('pHChart'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'pH Level',
                        borderColor: 'rgb(153, 102, 255)',
                        data: []
                    }]
                },
                options: { responsive: true }
            });
        }

        return {
            temperature: temperatureChart,
            humidity: humidityChart,
            oxygen: oxygenChart,
            pH: pHChart
        };
    }

    // Load sensor data from database and update charts
    function loadSensorData() {
        fetch('php/get_sensor_data.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                    });
                }
                return response.json();
            })
            .then(data => {
                // Handle both direct array and success response patterns
                const readings = Array.isArray(data) ? data : (data.data || []);
                
                sensorTable.innerHTML = '';
                
                if (readings && readings.length > 0) {
                    // Update table
                    readings.forEach(reading => {
                        const row = sensorTable.insertRow();
                        row.innerHTML = `
                            <td>${reading.sensor_id || ''}</td>
                            <td>${reading.humidity || 'N/A'}</td>
                            <td>${reading.oxygen_level || 'N/A'}</td>
                            <td>${reading.ph_level || 'N/A'}</td>
                            <td>${reading.temperature || 'N/A'}</td>
                            <td>${reading.reading_timestamp ? formatTimestamp(reading.reading_timestamp) : 'N/A'}</td>
                            <td>
                                <button onclick="editSensorData('${reading.sensor_id}')">Edit</button>
                                <button onclick="deleteSensorData('${reading.sensor_id}')">Delete</button>
                            </td>
                        `;
                    });
                    
                    // Update charts
                    updateCharts(readings, charts);
                } else {
                    sensorTable.innerHTML = '<tr><td colspan="7">No sensor data found</td></tr>';
                    // Clear charts if no data
                    updateCharts([], charts);
                }
            })
            .catch(error => {
                console.error('Error loading sensor data:', error);
                sensorTable.innerHTML = '<tr><td colspan="7">Error loading sensor data</td></tr>';
                // Clear charts on error
                updateCharts([], charts);
            });
    }

    function updateCharts(data, charts) {
        // First, check if we have any data
        if (!data || data.length === 0) {
            // Clear all charts if no data
            Object.values(charts).forEach(chart => {
                chart.data.datasets[0].data = [];
                chart.update();
            });
            return;
        }

        const chartData = {
            temperature: [],
            humidity: [],
            oxygen: [],
            pH: []
        };

        // Sort data by timestamp to ensure proper line chart rendering
        const sortedData = data.sort((a, b) => new Date(a.reading_timestamp) - new Date(b.reading_timestamp));

        sortedData.forEach(reading => {
            // Validate timestamp
            const timestamp = new Date(reading.reading_timestamp);
            if (isNaN(timestamp.getTime())) {
                console.warn('Invalid timestamp:', reading.reading_timestamp);
                return;
            }

            // Only add data points with valid numeric values
            if (reading.temperature !== null && reading.temperature !== undefined && !isNaN(reading.temperature)) {
                chartData.temperature.push({ x: timestamp, y: parseFloat(reading.temperature) });
            }
            if (reading.humidity !== null && reading.humidity !== undefined && !isNaN(reading.humidity)) {
                chartData.humidity.push({ x: timestamp, y: parseFloat(reading.humidity) });
            }
            if (reading.oxygen_level !== null && reading.oxygen_level !== undefined && !isNaN(reading.oxygen_level)) {
                chartData.oxygen.push({ x: timestamp, y: parseFloat(reading.oxygen_level) });
            }
            if (reading.ph_level !== null && reading.ph_level !== undefined && !isNaN(reading.ph_level)) {
                chartData.pH.push({ x: timestamp, y: parseFloat(reading.ph_level) });
            }
        });

        // Update chart data
        charts.temperature.data.datasets[0].data = chartData.temperature;
        charts.humidity.data.datasets[0].data = chartData.humidity;
        charts.oxygen.data.datasets[0].data = chartData.oxygen;
        charts.pH.data.datasets[0].data = chartData.pH;

        // Update charts with error handling
        Object.entries(charts).forEach(([chartName, chart]) => {
            try {
                chart.update('none'); // Use 'none' mode for better performance
            } catch (error) {
                console.error(`Error updating ${chartName} chart:`, error);
                // Try to reset chart scales if update fails
                chart.resetZoom();
                try {
                    chart.update();
                } catch (secondError) {
                    console.error(`Failed to recover ${chartName} chart:`, secondError);
                }
            }
        });
    }
});

function formatTimestamp(timestamp) {
    return new Date(timestamp).toLocaleString();
}

// Edit sensor data function
function editSensorData(sensorId) {
    fetch(`php/get_sensor_reading.php?id=${sensorId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(reading => {
            if (!reading || !reading.sensor_id) {
                throw new Error('Sensor reading not found');
            }
            document.getElementById('sensorId').value = reading.sensor_id || '';
            document.getElementById('humidity').value = reading.humidity || '';
            document.getElementById('oxygenLevel').value = reading.oxygen_level || '';
            document.getElementById('pH').value = reading.ph_level || '';
            document.getElementById('temperature').value = reading.temperature || '';
            document.getElementById('readingTimestamp').value = reading.reading_timestamp ? reading.reading_timestamp.slice(0, 16) : '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading sensor reading details. Please try again.');
        });
}

// Delete sensor data function
function deleteSensorData(sensorId) {
    if (confirm('Are you sure you want to delete this sensor reading?')) {
        fetch(`php/delete_sensor_data.php?id=${sensorId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sensor reading deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting sensor reading: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting sensor reading. Please try again.');
        });
    }
}
