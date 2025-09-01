document.addEventListener('DOMContentLoaded', function() {
    const rotationForm = document.getElementById('rotationForm');
    const rotationTable = document.getElementById('rotationTable').getElementsByTagName('tbody')[0];

    // Load existing rotations
    loadRotations();

    // Form submission handler
    rotationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            rotationId: document.getElementById('rotationId').value,
            strategy: document.getElementById('strategy').value,
            rotationDate: document.getElementById('rotationDate').value,
            rotationType: document.getElementById('rotationType').value
        };

        // Send data to PHP backend
        fetch('php/save_rotation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rotation saved successfully!');
                const newRow = rotationTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.rotationId}</td>
                        <td>${formatStrategy(formData.strategy)}</td>
                        <td>${formData.rotationDate}</td>
                        <td>${formatRotationType(formData.rotationType)}</td>
                        <td>
                            <button onclick=\"editRotation('${formData.rotationId}')\">Edit</button>
                            <button onclick=\"deleteRotation('${formData.rotationId}')\">Delete</button>
                        </td>
                    `;
                rotationForm.reset();
            } else {
                alert('Error saving rotation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving rotation. Please try again.');
        });
    });

    // Load rotations from database
    function loadRotations() {
        fetch('php/get_rotations.php')
            .then(response => response.json())
            .then(data => {
                rotationTable.innerHTML = '';
                data.forEach(rotation => {
                    const row = rotationTable.insertRow();
                    row.innerHTML = `
                        <td>${rotation.rotation_id}</td>
                        <td>${formatStrategy(rotation.strategy)}</td>
                        <td>${rotation.rotation_date}</td>
                        <td>${formatRotationType(rotation.rotation_type)}</td>
                        <td>
                            <button onclick="editRotation('${rotation.rotation_id}')">Edit</button>
                            <button onclick="deleteRotation('${rotation.rotation_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading rotations. Please try again.');
            });
    }
});

// Format strategy for display
function formatStrategy(strategy) {
    return strategy.toUpperCase();
}

// Format rotation type for display
function formatRotationType(type) {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Edit rotation function
function editRotation(rotationId) {
    fetch(`php/get_rotation.php?id=${rotationId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(rotation => {
            if (!rotation || !rotation.rotation_id) {
                throw new Error('Rotation not found');
            }
            document.getElementById('rotationId').value = rotation.rotation_id;
            document.getElementById('strategy').value = rotation.strategy;
            document.getElementById('rotationDate').value = rotation.rotation_date;
            document.getElementById('rotationType').value = rotation.rotation_type;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading rotation details. Please try again.');
        });
}

// Delete rotation function
function deleteRotation(rotationId) {
    if (confirm('Are you sure you want to delete this rotation?')) {
        fetch(`php/delete_rotation.php?id=${rotationId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rotation deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting rotation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting rotation. Please try again.');
        });
    }
}
