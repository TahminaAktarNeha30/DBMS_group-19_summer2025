document.addEventListener('DOMContentLoaded', function() {
    const spoilageForm = document.getElementById('spoilageForm');
    const spoilageTable = document.getElementById('spoilageTable').getElementsByTagName('tbody')[0];

    loadSpoilageRecords();

    spoilageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = {
            recordId: document.getElementById('recordId').value,
            batchId: document.getElementById('batchId').value,
            quantityLost: document.getElementById('quantityLost').value,
            reason: document.getElementById('reason').value,
            spoilageDate: document.getElementById('spoilageDate').value
        };

        fetch('php/save_spoilage_record.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(async response => {
            const text = await response.text();
            try { return JSON.parse(text); } catch { throw new Error(text); }
        })
        .then(data => {
            if (data.success) {
                alert('Spoilage record saved successfully!');
                const newRow = spoilageTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.recordId}</td>
                        <td>${formData.batchId}</td>
                        <td>${formData.quantityLost}</td>
                        <td>${formData.reason}</td>
                        <td>${formData.spoilageDate}</td>
                        <td>
                            <button onclick="editSpoilage('${formData.recordId}')">Edit</button>
                            <button onclick="deleteSpoilage('${formData.recordId}')">Delete</button>
                        </td>
                    `;
                spoilageForm.reset();
            } else {
                alert('Error saving spoilage record: ' + (data.message || data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error saving spoilage record.');
        });
    });

    function loadSpoilageRecords() {
        fetch('php/get_spoilage_records.php')
            .then(async response => {
                const text = await response.text();
                try { return JSON.parse(text); } catch { throw new Error(text || 'Empty response'); }
            })
            .then(data => {
                spoilageTable.innerHTML = '';
                (data || []).forEach(rec => {
                    const row = spoilageTable.insertRow();
                    row.innerHTML = `
                        <td>${rec.record_id || ''}</td>
                        <td>${rec.batch_id || ''}</td>
                        <td>${rec.quantity_lost ?? ''}</td>
                        <td>${rec.reason || ''}</td>
                        <td>${rec.spoilage_date || ''}</td>
                        <td>
                            <button onclick="editSpoilage('${rec.record_id}')">Edit</button>
                            <button onclick="deleteSpoilage('${rec.record_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error loading spoilage records.');
            });
    }
});

function editSpoilage(recordId) {
    fetch(`php/get_spoilage_record.php?id=${recordId}`)
        .then(async response => {
            const text = await response.text();
            try { return JSON.parse(text); } catch { throw new Error(text || 'Empty response'); }
        })
        .then(rec => {
            document.getElementById('recordId').value = rec.record_id || '';
            document.getElementById('batchId').value = rec.batch_id || '';
            document.getElementById('quantityLost').value = rec.quantity_lost ?? '';
            document.getElementById('reason').value = rec.reason || '';
            document.getElementById('spoilageDate').value = rec.spoilage_date || '';
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error loading spoilage record.');
        });
}

function deleteSpoilage(recordId) {
    if (!confirm('Are you sure you want to delete this spoilage record?')) return;
    fetch(`php/delete_spoilage_record.php?id=${recordId}`, { method: 'DELETE' })
        .then(async response => {
            const text = await response.text();
            try { return JSON.parse(text); } catch { throw new Error(text || 'Empty response'); }
        })
        .then(data => {
            if (data.success) {
                alert('Spoilage record deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting spoilage record: ' + (data.message || data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error deleting spoilage record.');
        });
}


