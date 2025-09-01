document.addEventListener('DOMContentLoaded', function() {
    const factoryForm = document.getElementById('factoryForm');
    const factoryTable = document.getElementById('factoryTable').getElementsByTagName('tbody')[0];

    // Load existing factories
    loadFactories();

    // Form submission handler
    factoryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            factoryId: document.getElementById('factoryId').value,
            name: document.getElementById('name').value,
            location: document.getElementById('location').value,
            processingCapacity: document.getElementById('processingCapacity').value,
            capacityUnit: document.getElementById('capacityUnit').value
        };

        // Send data to PHP backend
        fetch('php/save_factory.php', {
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
                alert('Factory saved successfully!');
                const newRow = factoryTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.factoryId}</td>
                        <td>${formData.name}</td>
                        <td>${formData.location}</td>
                        <td>${formData.processingCapacity}</td>
                        <td>${formatCapacityUnit(formData.capacityUnit)}</td>
                        <td>
                            <button onclick="editFactory('${formData.factoryId}')">Edit</button>
                            <button onclick="deleteFactory('${formData.factoryId}')">Delete</button>
                        </td>
                    `;
                factoryForm.reset();
            } else {
                alert('Error saving factory: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving factory: ' + error.message);
        });
    });

    // Load factories from database
    function loadFactories() {
        fetch('php/get_factories.php')
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
                const factories = Array.isArray(data) ? data : (data.data || []);
                factoryTable.innerHTML = '';
                factories.forEach(factory => {
                    const row = factoryTable.insertRow();
                    row.innerHTML = `
                        <td>${factory.factory_id || ''}</td>
                        <td>${factory.name || ''}</td>
                        <td>${factory.location || ''}</td>
                        <td>${factory.processing_capacity || ''}</td>
                        <td>${formatCapacityUnit(factory.capacity_unit || '')}</td>
                        <td>
                            <button onclick="editFactory('${factory.factory_id}')">Edit</button>
                            <button onclick="deleteFactory('${factory.factory_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading factories: ' + error.message);
            });
    }
});

// Format capacity unit for display
function formatCapacityUnit(unit) {
    return unit.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Edit factory function
function editFactory(factoryId) {
    fetch(`php/get_factory.php?id=${factoryId}`)
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
        .then(factory => {
            if (!factory || !factory.factory_id) {
                throw new Error('Factory not found');
            }
            document.getElementById('factoryId').value = factory.factory_id || '';
            document.getElementById('name').value = factory.name || '';
            document.getElementById('location').value = factory.location || '';
            document.getElementById('processingCapacity').value = factory.processing_capacity || '';
            document.getElementById('capacityUnit').value = factory.capacity_unit || '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading factory details: ' + error.message);
        });
}

// Delete factory function
function deleteFactory(factoryId) {
    if (confirm('Are you sure you want to delete this factory?')) {
        fetch(`php/delete_factory.php?id=${factoryId}`, {
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
                alert('Factory deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting factory: ' + (data.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting factory: ' + error.message);
        });
    }
}
