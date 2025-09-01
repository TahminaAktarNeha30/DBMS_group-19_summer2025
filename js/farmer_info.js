document.addEventListener('DOMContentLoaded', function() {
    const farmerForm = document.getElementById('farmerForm');
    const farmerTable = document.getElementById('farmerTable').getElementsByTagName('tbody')[0];

    // Load existing farmers
    loadFarmers();

    // Form submission handler
    farmerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            farmerId: document.getElementById('farmerId').value,
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            phone: document.getElementById('phone').value
        };

        // Send data to PHP backend
        fetch('php/save_farmer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Farmer information saved successfully!');
                const newRow = farmerTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.farmerId}</td>
                        <td>${formData.firstName}</td>
                        <td>${formData.lastName}</td>
                        <td>${formatPhoneNumber(formData.phone)}</td>
                        <td>
                            <button onclick="editFarmer('${formData.farmerId}')">Edit</button>
                            <button onclick="deleteFarmer('${formData.farmerId}')">Delete</button>
                        </td>
                    `;
                farmerForm.reset();
            } else {
                alert('Error saving farmer information: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving farmer information. Please try again.');
        });
    });

    // Load farmers from database
    function loadFarmers() {
        fetch('php/get_farmers.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text.substring(0, 200));
                        throw new Error('Server returned non-JSON response');
                    });
                }
                return response.json();
            })
            .then(data => {
                farmerTable.innerHTML = '';
                
                // Handle multiple response formats
                if (Array.isArray(data)) {
                    // Direct array response (normal case)
                    if (data.length === 0) {
                        farmerTable.innerHTML = '<tr><td colspan="5">No farmers found</td></tr>';
                        return;
                    }
                    
                    data.forEach(farmer => {
                        if (farmer && farmer.farmer_id) {
                            const row = farmerTable.insertRow();
                            row.innerHTML = `
                                <td>${farmer.farmer_id || ''}</td>
                                <td>${farmer.first_name || ''}</td>
                                <td>${farmer.last_name || ''}</td>
                                <td>${formatPhoneNumber(farmer.phone || '')}</td>
                                <td>
                                    <button onclick="editFarmer('${farmer.farmer_id}')">Edit</button>
                                    <button onclick="deleteFarmer('${farmer.farmer_id}')">Delete</button>
                                </td>
                            `;
                        }
                    });
                } else if (data && data.success === false) {
                    // Error response object
                    throw new Error(data.error || data.message || 'Failed to load farmers');
                } else if (data && data.data && Array.isArray(data.data)) {
                    // Wrapped array response
                    const farmers = data.data;
                    if (farmers.length === 0) {
                        farmerTable.innerHTML = '<tr><td colspan="5">No farmers found</td></tr>';
                        return;
                    }
                    
                    farmers.forEach(farmer => {
                        if (farmer && farmer.farmer_id) {
                            const row = farmerTable.insertRow();
                            row.innerHTML = `
                                <td>${farmer.farmer_id || ''}</td>
                                <td>${farmer.first_name || ''}</td>
                                <td>${farmer.last_name || ''}</td>
                                <td>${formatPhoneNumber(farmer.phone || '')}</td>
                                <td>
                                    <button onclick="editFarmer('${farmer.farmer_id}')">Edit</button>
                                    <button onclick="deleteFarmer('${farmer.farmer_id}')">Delete</button>
                                </td>
                            `;
                        }
                    });
                } else {
                    // Unknown response format
                    console.error('Unexpected response format:', data);
                    throw new Error('Unexpected response format from server');
                }
            })
            .catch(error => {
                console.error('Error loading farmers:', error);
                farmerTable.innerHTML = '<tr><td colspan="5">Error loading farmer information</td></tr>';
                // Don't show alert for empty results, only for actual errors
                if (error.message !== 'No farmers found') {
                    alert('Error loading farmer information. Please check console for details.');
                }
            });
    }
});

// Format phone number for display
function formatPhoneNumber(phone) {
    const cleaned = ('' + phone).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return '(' + match[1] + ') ' + match[2] + '-' + match[3];
    }
    return phone;
}

// Edit farmer function
function editFarmer(farmerId) {
    fetch(`php/get_farmer.php?id=${farmerId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(farmer => {
            if (!farmer || !farmer.farmer_id) {
                throw new Error('Farmer not found');
            }
            document.getElementById('farmerId').value = farmer.farmer_id;
            document.getElementById('firstName').value = farmer.first_name;
            document.getElementById('lastName').value = farmer.last_name;
            document.getElementById('phone').value = farmer.phone;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading farmer details. Please try again.');
        });
}

// Delete farmer function
function deleteFarmer(farmerId) {
    if (confirm('Are you sure you want to delete this farmer?')) {
        fetch(`php/delete_farmer.php?id=${farmerId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Farmer deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting farmer: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting farmer. Please try again.');
        });
    }
}
