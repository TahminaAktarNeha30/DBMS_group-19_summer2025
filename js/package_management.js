document.addEventListener('DOMContentLoaded', function() {
    const packageForm = document.getElementById('packageForm');
    const packageTable = document.getElementById('packageTable').getElementsByTagName('tbody')[0];

    // Load packages from database (define function first)
    window.loadPackages = function loadPackages() {
        fetch('php/get_packages.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Check if response is actually JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned HTML instead of JSON. Check PHP errors.');
                    });
                }
                return response.json();
            })
            .then(response => {
                if (!response.success) {
                    throw new Error(response.error || 'Failed to load packages');
                }
                const data = response.data || [];
                packageTable.innerHTML = '';
                if (data.length === 0) {
                    packageTable.innerHTML = '<tr><td colspan="8">No packages found</td></tr>';
                    return;
                }
                data.forEach(pkg => {
                    const row = packageTable.insertRow();
                    row.innerHTML = `
                        <td>${pkg.package_id || ''}</td>
                        <td>${pkg.name || ''}</td>
                        <td>${pkg.packaging_info || ''}</td>
                        <td>${pkg.type || ''}</td>
                        <td>${pkg.weight || ''}</td>
                        <td>${pkg.production_date || ''}</td>
                        <td>${pkg.expiration_date || ''}</td>
                        <td>
                            <button onclick="editPackage('${pkg.package_id}')">Edit</button>
                            <button onclick="deletePackage('${pkg.package_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading packages: ' + error.message + '\n\nPlease check:\n1. XAMPP MySQL is running\n2. Database schema has been updated\n3. Check browser console for details');
                packageTable.innerHTML = '<tr><td colspan="8">Error loading packages - check console</td></tr>';
            });
    };

    // Load existing packages
    window.loadPackages();

    // Form submission handler
    packageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            packageId: document.getElementById('packageId').value,
            name: document.getElementById('name').value,
            packagingInfo: document.getElementById('packagingInfo').value,
            type: document.getElementById('type').value,
            weight: document.getElementById('weight').value,
            productionDate: document.getElementById('productionDate').value,
            expirationDate: document.getElementById('expirationDate').value
        };

        // Validate expiration date is after production date
        if (new Date(formData.expirationDate) <= new Date(formData.productionDate)) {
            alert('Expiration date must be after production date');
            return;
        }

        // Send data to PHP backend
        fetch('php/save_package.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Package saved successfully!');
                // Append the new row instead of full reload
                const placeholderRow = packageTable.querySelector('tr td[colspan]');
                if (placeholderRow && placeholderRow.colSpan) {
                    packageTable.innerHTML = '';
                }
                const newRow = packageTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.packageId || ''}</td>
                        <td>${formData.name || ''}</td>
                        <td>${formData.packagingInfo || ''}</td>
                        <td>${formData.type || ''}</td>
                        <td>${formData.weight || ''}</td>
                        <td>${formData.productionDate || ''}</td>
                        <td>${formData.expirationDate || ''}</td>
                        <td>
                            <button onclick="editPackage('${formData.packageId}')">Edit</button>
                            <button onclick="deletePackage('${formData.packageId}')">Delete</button>
                        </td>
                    `;
                packageForm.reset();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving package: ' + (error.error || error.message));
        });
    });
});

// Edit package function
function editPackage(packageId) {
    fetch(`php/get_package.php?id=${packageId}`)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(response => {
            // Handle both direct data and structured responses
            const pkg = response.success !== undefined ? response.data : response;
            if (!pkg || !pkg.package_id) {
                throw new Error('Package not found');
            }
            document.getElementById('packageId').value = pkg.package_id || '';
            document.getElementById('name').value = pkg.name || '';
            document.getElementById('packagingInfo').value = pkg.packaging_info || '';
            document.getElementById('type').value = pkg.type || '';
            document.getElementById('weight').value = pkg.weight || '';
            // Convert datetime to date format for HTML input fields
            const productionDate = pkg.production_date ? pkg.production_date.split(' ')[0] : '';
            const expirationDate = pkg.expiration_date ? pkg.expiration_date.split(' ')[0] : '';
            document.getElementById('productionDate').value = productionDate;
            document.getElementById('expirationDate').value = expirationDate;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading package details: ' + (error.error || error.message));
        });
}

// Delete package function
function deletePackage(packageId) {
    if (confirm('Are you sure you want to delete this package?')) {
        fetch(`php/delete_package.php?id=${packageId}`, {
            method: 'DELETE'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Package deleted successfully!');
                window.loadPackages();
            } else {
                throw new Error(data.error || 'Failed to delete package');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting package: ' + (error.error || error.message));
        });
    }
}
