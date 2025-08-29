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
        .then(response => response.json())
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
                alert('Error saving farm: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving farm. Please try again.');
        });
    });

    // Load farms from database
    function loadFarms() {
        fetch('php/get_farms.php')
            .then(response => response.json())
            .then(data => {
                farmTable.innerHTML = '';
                data.forEach(farm => {
                    const row = farmTable.insertRow();
                    row.innerHTML = `
                        <td>${farm.farm_id}</td>
                        <td>${farm.location}</td>
                        <td>${farm.size}</td>
                        <td>${formatSoilType(farm.soil_type)}</td>
                        <td>${formatIrrigationMethod(farm.irrigation_method)}</td>
                        <td>
                            <button onclick="editFarm('${farm.farm_id}')">Edit</button>
                            <button onclick="deleteFarm('${farm.farm_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading farms. Please try again.');
            });
    }
});

// Format soil type for display
function formatSoilType(type) {
    return type.charAt(0).toUpperCase() + type.slice(1);
}

// Format irrigation method for display
function formatIrrigationMethod(method) {
    return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Edit farm function
function editFarm(farmId) {
    fetch(`php/get_farm.php?id=${farmId}`)
        .then(response => response.json())
        .then(farm => {
            document.getElementById('farmId').value = farm.farm_id;
            document.getElementById('location').value = farm.location;
            document.getElementById('size').value = farm.size;
            document.getElementById('soilType').value = farm.soil_type;
            document.getElementById('irrigationMethod').value = farm.irrigation_method;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading farm details. Please try again.');
        });
}

// Delete farm function
function deleteFarm(farmId) {
    if (confirm('Are you sure you want to delete this farm?')) {
        fetch(`php/delete_farm.php?id=${farmId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Farm deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting farm: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting farm. Please try again.');
        });
    }
}
