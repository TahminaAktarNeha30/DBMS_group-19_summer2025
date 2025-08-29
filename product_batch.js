document.addEventListener('DOMContentLoaded', function() {
    const batchForm = document.getElementById('batchForm');
    const batchTable = document.getElementById('batchTable').getElementsByTagName('tbody')[0];

    // Load existing batches
    loadBatches();

    // Form submission handler
    batchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            batchId: document.getElementById('batchId').value,
            packageCount: document.getElementById('packageCount').value,
            batchWeight: document.getElementById('batchWeight').value,
            qualityStatus: document.getElementById('qualityStatus').value,
            creationDate: document.getElementById('creationDate').value
        };

        // Send data to PHP backend
        fetch('php/save_batch.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Batch saved successfully!');
                const newRow = batchTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.batchId}</td>
                        <td>${formData.packageCount}</td>
                        <td>${formData.batchWeight}</td>
                        <td>${formData.qualityStatus}</td>
                        <td>${formData.creationDate}</td>
                        <td>
                            <button onclick="editBatch('${formData.batchId}')">Edit</button>
                            <button onclick="deleteBatch('${formData.batchId}')">Delete</button>
                        </td>
                    `;
                batchForm.reset();
            } else {
                alert('Error saving batch: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving batch. Please try again.');
        });
    });

    // Load batches from database
    function loadBatches() {
        fetch('php/get_batches.php')
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(text);
                }
            })
            .then(data => {
                batchTable.innerHTML = '';
                data.forEach(batch => {
                    const row = batchTable.insertRow();
                    row.innerHTML = `
                        <td>${batch.batch_id}</td>
                        <td>${batch.package_count}</td>
                        <td>${batch.batch_weight}</td>
                        <td>${batch.quality_status}</td>
                        <td>${batch.creation_date}</td>
                        <td>
                            <button onclick="editBatch('${batch.batch_id}')">Edit</button>
                            <button onclick="deleteBatch('${batch.batch_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading batches. Please try again.');
            });
    }
});

// Edit batch function
function editBatch(batchId) {
    fetch(`php/get_batch.php?id=${batchId}`)
        .then(response => response.json())
        .then(batch => {
            document.getElementById('batchId').value = batch.batch_id;
            document.getElementById('packageCount').value = batch.package_count;
            document.getElementById('batchWeight').value = batch.batch_weight;
            document.getElementById('qualityStatus').value = batch.quality_status;
            document.getElementById('creationDate').value = batch.creation_date;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading batch details. Please try again.');
        });
}

// Delete batch function
function deleteBatch(batchId) {
    if (confirm('Are you sure you want to delete this batch?')) {
        fetch(`php/delete_batch.php?id=${batchId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Batch deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting batch: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting batch. Please try again.');
        });
    }
}
