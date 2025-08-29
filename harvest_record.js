document.addEventListener('DOMContentLoaded', function() {
    const harvestForm = document.getElementById('harvestForm');
    const harvestTable = document.getElementById('harvestTable').getElementsByTagName('tbody')[0];

    // Load existing harvest records
    loadHarvestRecords();

    // Form submission handler
    harvestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            recordId: document.getElementById('recordId').value,
            status: document.getElementById('status').value,
            quantity: document.getElementById('quantity').value,
            harvestTime: document.getElementById('harvestTime').value,
            sowingDate: document.getElementById('sowingDate').value
        };

        // Validate dates
        if (new Date(formData.harvestTime) <= new Date(formData.sowingDate)) {
            alert('Harvest time must be after sowing date');
            return;
        }

        // Send data to PHP backend
        fetch('php/save_harvest_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Harvest record saved successfully!');
                const newRow = harvestTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.recordId}</td>
                        <td>${formatStatus(formData.status)}</td>
                        <td>${formData.quantity}</td>
                        <td>${formatDateTime(formData.harvestTime)}</td>
                        <td>${formData.sowingDate}</td>
                        <td>
                            <button onclick="editHarvestRecord('${formData.recordId}')">Edit</button>
                            <button onclick="deleteHarvestRecord('${formData.recordId}')">Delete</button>
                        </td>
                    `;
                harvestForm.reset();
            } else {
                alert('Error saving harvest record: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving harvest record. Please try again.');
        });
    });

    // Load harvest records from database
    function loadHarvestRecords() {
        fetch('php/get_harvest_records.php')
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(text);
                }
            })
            .then(data => {
                harvestTable.innerHTML = '';
                data.forEach(record => {
                    const row = harvestTable.insertRow();
                    row.innerHTML = `
                        <td>${record.record_id}</td>
                        <td>${formatStatus(record.status)}</td>
                        <td>${record.quantity}</td>
                        <td>${formatDateTime(record.harvest_time)}</td>
                        <td>${record.sowing_date}</td>
                        <td>
                            <button onclick="editHarvestRecord('${record.record_id}')">Edit</button>
                            <button onclick="deleteHarvestRecord('${record.record_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading harvest records. Please try again.');
            });
    }
});

// Format status for display
function formatStatus(status) {
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Format datetime for display
function formatDateTime(datetime) {
    return new Date(datetime).toLocaleString();
}

// Edit harvest record function
function editHarvestRecord(recordId) {
    fetch(`php/get_harvest_record.php?id=${recordId}`)
        .then(response => response.json())
        .then(record => {
            document.getElementById('recordId').value = record.record_id;
            document.getElementById('status').value = record.status;
            document.getElementById('quantity').value = record.quantity;
            document.getElementById('harvestTime').value = record.harvest_time.slice(0, 16);
            document.getElementById('sowingDate').value = record.sowing_date;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading harvest record details. Please try again.');
        });
}

// Delete harvest record function
function deleteHarvestRecord(recordId) {
    if (confirm('Are you sure you want to delete this harvest record?')) {
        fetch(`php/delete_harvest_record.php?id=${recordId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Harvest record deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting harvest record: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting harvest record. Please try again.');
        });
    }
}
