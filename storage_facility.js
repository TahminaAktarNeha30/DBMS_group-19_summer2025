document.addEventListener('DOMContentLoaded', function() {
    const storageForm = document.getElementById('storageForm');
    const storageTable = document.getElementById('storageTable').getElementsByTagName('tbody')[0];

    // Load existing storage facilities
    loadStorageFacilities();

    // Form submission handler
    storageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            storageId: document.getElementById('storageId').value,
            name: document.getElementById('name').value,
            type: document.getElementById('type').value,
            location: document.getElementById('location').value,
            capacity: document.getElementById('capacity').value,
            status: document.getElementById('status').value,
            entryDate: document.getElementById('entryDate').value
        };

        // Send data to PHP backend
        fetch('php/save_storage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Storage facility saved successfully!');
                const newRow = storageTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.storageId}</td>
                        <td>${formData.name}</td>
                        <td>${formatStorageType(formData.type)}</td>
                        <td>${formData.location}</td>
                        <td>${formData.capacity}</td>
                        <td>${formatStatus(formData.status)}</td>
                        <td>${formData.entryDate}</td>
                        <td>
                            <button onclick="editStorage('${formData.storageId}')">Edit</button>
                            <button onclick="deleteStorage('${formData.storageId}')">Delete</button>
                        </td>
                    `;
                storageForm.reset();
            } else {
                alert('Error saving storage facility: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving storage facility. Please try again.');
        });
    });

    // Load storage facilities from database
    function loadStorageFacilities() {
        fetch('php/get_storage_facilities.php')
            .then(response => response.json())
            .then(data => {
                storageTable.innerHTML = '';
                data.forEach(facility => {
                    const row = storageTable.insertRow();
                    row.innerHTML = `
                        <td>${facility.storage_id}</td>
                        <td>${facility.name}</td>
                        <td>${formatStorageType(facility.type)}</td>
                        <td>${facility.location}</td>
                        <td>${facility.capacity}</td>
                        <td>${formatStatus(facility.status)}</td>
                        <td>${facility.entry_date}</td>
                        <td>
                            <button onclick="editStorage('${facility.storage_id}')">Edit</button>
                            <button onclick="deleteStorage('${facility.storage_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading storage facilities. Please try again.');
            });
    }
});

// Format storage type for display
function formatStorageType(type) {
    if (!type) return 'N/A';
    return String(type).split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Format status for display
function formatStatus(status) {
    if (!status) return 'N/A';
    const s = String(status);
    return s.charAt(0).toUpperCase() + s.slice(1);
}

// Edit storage facility function
function editStorage(storageId) {
    fetch(`php/get_storage.php?id=${storageId}`)
        .then(response => response.json())
        .then(facility => {
            document.getElementById('storageId').value = facility.storage_id;
            document.getElementById('name').value = facility.name;
            document.getElementById('type').value = facility.type || '';
            document.getElementById('location').value = facility.location;
            document.getElementById('capacity').value = facility.capacity;
            document.getElementById('status').value = facility.status || '';
            document.getElementById('entryDate').value = facility.entry_date || '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading storage facility details. Please try again.');
        });
}

// Delete storage facility function
function deleteStorage(storageId) {
    if (confirm('Are you sure you want to delete this storage facility?')) {
        fetch(`php/delete_storage.php?id=${storageId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Storage facility deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting storage facility: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting storage facility. Please try again.');
        });
    }
}
