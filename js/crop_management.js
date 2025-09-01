document.addEventListener('DOMContentLoaded', function() {
    const cropForm = document.getElementById('cropForm');
    const cropTable = document.getElementById('cropTable').getElementsByTagName('tbody')[0];

    // Load existing crops
    loadCrops();

    // Form submission handler
    cropForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            cropId: document.getElementById('cropId').value,
            cropName: document.getElementById('cropName').value,
            category: document.getElementById('category').value,
            waterRequirements: document.getElementById('waterRequirements').value,
            soilPreference: document.getElementById('soilPreference').value
        };

        // Send data to PHP backend
        fetch('php/save_crop.php', {
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
                alert('Crop saved successfully!');
                // Append the new row instead of reloading all crops
                const placeholderRow = cropTable.querySelector('tr td[colspan]');
                if (placeholderRow && placeholderRow.colSpan) {
                    cropTable.innerHTML = '';
                }
                const newRow = cropTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.cropId}</td>
                        <td>${formData.cropName}</td>
                        <td>${formatCategory(formData.category)}</td>
                        <td>${document.getElementById('waterRequirements').value} mm/day</td>
                        <td>${formatSoilPreference(document.getElementById('soilPreference').value)}</td>
                        <td>
                            <button onclick="editCrop('${formData.cropId}')">Edit</button>
                            <button onclick="deleteCrop('${formData.cropId}')">Delete</button>
                        </td>
                    `;
                cropForm.reset();
            } else {
                alert('Error saving crop: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving crop:', error);
            alert('Error saving crop: ' + error.message);
        });
    });

    // Load crops from database
    function loadCrops() {
        fetch('php/get_crops.php')
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
                const crops = Array.isArray(data) ? data : (data.data || []);
                
                cropTable.innerHTML = '';
                
                if (crops && crops.length > 0) {
                    crops.forEach(crop => {
                        const row = cropTable.insertRow();
                        row.innerHTML = `
                            <td>${crop.crop_id || ''}</td>
                            <td>${crop.crop_name || ''}</td>
                            <td>${formatCategory(crop.category)}</td>
                            <td>${crop.water_requirements || 'N/A'} mm/day</td>
                            <td>${formatSoilPreference(crop.soil_preference)}</td>
                            <td>
                                <button onclick="editCrop('${crop.crop_id}')">Edit</button>
                                <button onclick="deleteCrop('${crop.crop_id}')">Delete</button>
                            </td>
                        `;
                    });
                } else {
                    cropTable.innerHTML = '<tr><td colspan="6">No crops found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading crops:', error);
                cropTable.innerHTML = '<tr><td colspan="6">Error loading crops</td></tr>';
                alert('Error loading crops: ' + error.message);
            });
    }
});

// Format category for display
function formatCategory(category) {
    if (!category) return 'N/A';
    const c = String(category);
    return c.charAt(0).toUpperCase() + c.slice(1);
}

// Format soil preference for display
function formatSoilPreference(soil) {
    if (!soil) return 'N/A';
    const s = String(soil);
    return s.charAt(0).toUpperCase() + s.slice(1);
}

// Edit crop function
function editCrop(cropId) {
    fetch(`php/get_crop.php?id=${cropId}`)
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
        .then(crop => {
            if (!crop || !crop.crop_id) {
                throw new Error('Crop not found');
            }
            document.getElementById('cropId').value = crop.crop_id || '';
            document.getElementById('cropName').value = crop.crop_name || '';
            document.getElementById('category').value = crop.category || '';
            document.getElementById('waterRequirements').value = crop.water_requirements || '';
            document.getElementById('soilPreference').value = crop.soil_preference || '';
        })
        .catch(error => {
            console.error('Error loading crop details:', error);
            alert('Error loading crop details: ' + error.message);
        });
}

// Delete crop function
function deleteCrop(cropId) {
    if (confirm('Are you sure you want to delete this crop?')) {
        fetch(`php/delete_crop.php?id=${cropId}`, {
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
                alert('Crop deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting crop: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting crop:', error);
            alert('Error deleting crop: ' + error.message);
        });
    }
}
