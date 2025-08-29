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
        .then(response => response.json())
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
                            <button onclick=\"editSensorData('${formData.sensorId}')\">Edit</button>
                            <button onclick=\"deleteSensorData('${formData.sensorId}')\">Delete</button>
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
                alert('Error saving sensor data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving sensor data. Please try again.');
        });
    });

    function initializeCharts() {
        const commonOptions = {
            responsive: true,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'minute'
                    }
                }
            }
        };

        const temperatureChart = new Chart(document.getElementById('temperatureChart'), {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Temperature (°C)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: []
                }]
            },
            options: commonOptions
        });

        const humidityChart = new Chart(document.getElementById('humidityChart'), {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Humidity (%)',
                    borderColor: 'rgb(54, 162, 235)',
                    data: []
                }]
            },
            options: commonOptions
        });

        const oxygenChart = new Chart(document.getElementById('oxygenChart'), {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Oxygen Level (%)',
                    borderColor: 'rgb(75, 192, 192)',
                    data: []
                }]
            },
            options: commonOptions
        });

        const pHChart = new Chart(document.getElementById('pHChart'), {
            type: 'line',
            data: {
                datasets: [{
                    label: 'pH Level',
                    borderColor: 'rgb(153, 102, 255)',
                    data: []
                }]
            },
            options: commonOptions
        });

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
            .then(response => response.json())
            .then(data => {
                sensorTable.innerHTML = '';
                
                // Update table
                data.forEach(reading => {
                    const row = sensorTable.insertRow();
                    row.innerHTML = `
                        <td>${reading.sensor_id}</td>
                        <td>${reading.humidity}</td>
                        <td>${reading.oxygen_level}</td>
                        <td>${reading.ph_level}</td>
                        <td>${reading.temperature}</td>
                        <td>${formatTimestamp(reading.reading_timestamp)}</td>
                        <td>
                            <button onclick="editSensorData('${reading.sensor_id}')">Edit</button>
                            <button onclick="deleteSensorData('${reading.sensor_id}')">Delete</button>
                        </td>
                    `;
                });

                // Update charts
                updateCharts(data, charts);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading sensor data. Please try again.');
            });
    }

    function updateCharts(data, charts) {
        const chartData = {
            temperature: [],
            humidity: [],
            oxygen: [],
            pH: []
        };

        data.forEach(reading => {
            const timestamp = new Date(reading.reading_timestamp);
            chartData.temperature.push({ x: timestamp, y: reading.temperature });
            chartData.humidity.push({ x: timestamp, y: reading.humidity });
            chartData.oxygen.push({ x: timestamp, y: reading.oxygen_level });
            chartData.pH.push({ x: timestamp, y: reading.ph_level });
        });

        charts.temperature.data.datasets[0].data = chartData.temperature;
        charts.humidity.data.datasets[0].data = chartData.humidity;
        charts.oxygen.data.datasets[0].data = chartData.oxygen;
        charts.pH.data.datasets[0].data = chartData.pH;

        Object.values(charts).forEach(chart => chart.update());
    }
});

function formatTimestamp(timestamp) {
    return new Date(timestamp).toLocaleString();
}

// Edit sensor data function
function editSensorData(sensorId) {
    fetch(`php/get_sensor_reading.php?id=${sensorId}`)
        .then(response => response.json())
        .then(reading => {
            document.getElementById('sensorId').value = reading.sensor_id;
            document.getElementById('humidity').value = reading.humidity;
            document.getElementById('oxygenLevel').value = reading.oxygen_level;
            document.getElementById('pH').value = reading.ph_level;
            document.getElementById('temperature').value = reading.temperature;
            document.getElementById('readingTimestamp').value = reading.reading_timestamp.slice(0, 16);
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
