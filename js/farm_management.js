document.addEventListener('DOMContentLoaded', function() {
    const farmForm = document.getElementById('farmForm');
    const farmTable = document.getElementById('farmTable').getElementsByTagName('tbody')[0];

    // Load existing farms
    loadFarms();

    // Form submission handler
    farmForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            farmId: document.getElementById('farmId').value,
            location: document.getElementById('location').value,
            size: document.getElementById('size').value,
            soilType: document.getElementById('soilType').value,
            irrigationMethod: document.getElementById('irrigationMethod').value
        };

        // Send data to PHP backend
        fetch('php/save_farm.php', {
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
                alert('Farm saved successfully!');
                const newRow = farmTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.farmId}</td>
                        <td>${formData.location}</td>
                        <td>${formData.size}</td>
                        <td>${formatSoilType(formData.soilType)}</td>
                        <td>${formatIrrigationMethod(formData.irrigationMethod)}</td>
                        <td>
                            <button onclick="editFarm('${formData.farmId}')">Edit</button>
                            <button onclick="deleteFarm('${formData.farmId}')">Delete</button>
                        </td>
                    `;
                farmForm.reset();
            } else {
                alert('Error saving farm: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving farm:', error);
            alert('Error saving farm: ' + error.message);
        });
    });

    // Load farms from database
    function loadFarms() {
        fetch('php/get_farms.php')
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
                const farms = Array.isArray(data) ? data : (data.data || []);
                
                farmTable.innerHTML = '';
                
                if (farms && farms.length > 0) {
                    farms.forEach(farm => {
                        const row = farmTable.insertRow();
                        row.innerHTML = `
                            <td>${farm.farm_id || ''}</td>
                            <td>${farm.location || ''}</td>
                            <td>${farm.size || ''}</td>
                            <td>${formatSoilType(farm.soil_type)}</td>
                            <td>${formatIrrigationMethod(farm.irrigation_method)}</td>
                            <td>
                                <button onclick="editFarm('${farm.farm_id}')">Edit</button>
                                <button onclick="deleteFarm('${farm.farm_id}')">Delete</button>
                            </td>
                        `;
                    });
                } else {
                    farmTable.innerHTML = '<tr><td colspan="6">No farms found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading farms:', error);
                farmTable.innerHTML = '<tr><td colspan="6">Error loading farms</td></tr>';
                alert('Error loading farms: ' + error.message);
            });
    }
});

// Format soil type for display
function formatSoilType(type) {
    if (!type) return 'N/A';
    return String(type).charAt(0).toUpperCase() + String(type).slice(1);
}

// Format irrigation method for display
function formatIrrigationMethod(method) {
    if (!method) return 'N/A';
    return String(method).split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Edit farm function
function editFarm(farmId) {
    fetch(`php/get_farm.php?id=${farmId}`)
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
        .then(farm => {
            if (!farm || !farm.farm_id) {
                throw new Error('Farm not found');
            }
            document.getElementById('farmId').value = farm.farm_id || '';
            document.getElementById('location').value = farm.location || '';
            document.getElementById('size').value = farm.size || '';
            document.getElementById('soilType').value = farm.soil_type || '';
            document.getElementById('irrigationMethod').value = farm.irrigation_method || '';
        })
        .catch(error => {
            console.error('Error loading farm details:', error);
            alert('Error loading farm details: ' + error.message);
        });
}

// Delete farm function
function deleteFarm(farmId) {
    if (confirm('Are you sure you want to delete this farm?')) {
        fetch(`php/delete_farm.php?id=${farmId}`, {
            method: 'DELETE'
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
                alert('Farm deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting farm: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting farm:', error);
            alert('Error deleting farm: ' + error.message);
        });
    }
}
