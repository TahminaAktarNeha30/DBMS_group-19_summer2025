document.addEventListener('DOMContentLoaded', function() {
    const preHarvestForm = document.getElementById('preHarvestForm');
    const preHarvestTable = document.getElementById('preHarvestTable').getElementsByTagName('tbody')[0];

    // Load existing pre-harvest materials
    loadPreHarvestMaterials();

    // Form submission handler
    preHarvestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            preHarvestId: document.getElementById('preHarvestId').value,
            name: document.getElementById('name').value,
            quantity: document.getElementById('quantity').value,
            type: document.getElementById('type').value,
            harvestingDate: document.getElementById('harvestingDate').value,
            expiryDate: document.getElementById('expiryDate').value
        };

        // Validate dates
        if (new Date(formData.expiryDate) <= new Date(formData.harvestingDate)) {
            alert('Expiry date must be after harvesting date');
            return;
        }

        // Send data to PHP backend
        fetch('php/save_pre_harvest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pre-harvest material saved successfully!');
                const newRow = preHarvestTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.preHarvestId}</td>
                        <td>${formData.name}</td>
                        <td>${formData.quantity}</td>
                        <td>${formatType(formData.type)}</td>
                        <td>${formData.harvestingDate}</td>
                        <td>${formData.expiryDate}</td>
                        <td>
                            <button onclick="editPreHarvest('${formData.preHarvestId}')">Edit</button>
                            <button onclick="deletePreHarvest('${formData.preHarvestId}')">Delete</button>
                        </td>
                    `;
                preHarvestForm.reset();
            } else {
                alert('Error saving pre-harvest material: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving pre-harvest material. Please try again.');
        });
    });

    // Load pre-harvest materials from database
    function loadPreHarvestMaterials() {
        fetch('php/get_pre_harvest_materials.php')
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(text || 'Empty response');
                }
            })
            .then(data => {
                preHarvestTable.innerHTML = '';
                data.forEach(material => {
                    const row = preHarvestTable.insertRow();
                    row.innerHTML = `
                        <td>${material.pre_harvest_id}</td>
                        <td>${material.name}</td>
                        <td>${material.quantity}</td>
                        <td>${formatType(material.type)}</td>
                        <td>${material.harvesting_date}</td>
                        <td>${material.expiry_date}</td>
                        <td>
                            <button onclick="editPreHarvest('${material.pre_harvest_id}')">Edit</button>
                            <button onclick="deletePreHarvest('${material.pre_harvest_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading pre-harvest materials. Please try again.');
            });
    }
});

// Format type for display
function formatType(type) {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Edit pre-harvest material function
function editPreHarvest(preHarvestId) {
    fetch(`php/get_pre_harvest.php?id=${preHarvestId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(material => {
            if (!material || !material.pre_harvest_id) {
                throw new Error('Pre-harvest material not found');
            }
            document.getElementById('preHarvestId').value = material.pre_harvest_id;
            document.getElementById('name').value = material.name;
            document.getElementById('quantity').value = material.quantity;
            document.getElementById('type').value = material.type;
            document.getElementById('harvestingDate').value = material.harvesting_date;
            document.getElementById('expiryDate').value = material.expiry_date;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading pre-harvest material details. Please try again.');
        });
}

// Delete pre-harvest material function
function deletePreHarvest(preHarvestId) {
    if (confirm('Are you sure you want to delete this pre-harvest material?')) {
        fetch(`php/delete_pre_harvest.php?id=${preHarvestId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pre-harvest material deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting pre-harvest material: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting pre-harvest material. Please try again.');
        });
    }
}
