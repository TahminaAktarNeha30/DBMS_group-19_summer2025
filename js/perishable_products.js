document.addEventListener('DOMContentLoaded', function() {
    const perishableProductForm = document.getElementById('perishableProductForm');
    const perishableProductsTable = document.getElementById('perishableProductsTable').getElementsByTagName('tbody')[0];
    const clearFormBtn = document.getElementById('clearForm');

    // Load suppliers and products on page load
    // loadSuppliers(); // Removed since we're using text input
    loadPerishableProducts();
    checkTableStatus();

    // Form submission handler
    perishableProductForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            productId: document.getElementById('productId').value,
            productName: document.getElementById('productName').value,
            category: document.getElementById('category').value,
            shelfLifeDays: document.getElementById('shelfLifeDays').value,
            packagingType: document.getElementById('packagingType').value,
            quantity: document.getElementById('quantity').value
        };

        // Client-side validation
        if (parseInt(formData.shelfLifeDays) <= 0) {
            alert('Shelf life must be greater than 0 days');
            return;
        }

        // Send data to PHP backend
        fetch('php/save_perishable_product.php', {
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
            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Server returned HTML instead of JSON. Response: ' + text.substring(0, 200));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Perishable product saved successfully!');
                loadPerishableProducts();
                perishableProductForm.reset();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving perishable product: ' + error.message + '\n\nPlease check:\n1. XAMPP MySQL is running\n2. Database table exists\n3. Check browser console for details');
        });
    });

    // Clear form handler
    clearFormBtn.addEventListener('click', function() {
        perishableProductForm.reset();
    });

    // Load suppliers for dropdown - removed since we're using text input now
    // function loadSuppliers() { ... }

    // Load perishable products from database
    function loadPerishableProducts() {
        fetch('php/get_perishable_products.php')
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
                    throw new Error(response.error || 'Failed to load perishable products');
                }
                
                const data = response.data || [];
                perishableProductsTable.innerHTML = '';
                
                if (data.length === 0) {
                    perishableProductsTable.innerHTML = '<tr><td colspan="7">No perishable products found</td></tr>';
                    return;
                }
                
                data.forEach(product => {
                    const row = perishableProductsTable.insertRow();
                    row.innerHTML = `
                        <td>${product.product_id || ''}</td>
                        <td>${product.product_name || ''}</td>
                        <td>${product.category || ''}</td>
                        <td>${product.shelf_life_days || ''} days</td>
                        <td>${product.packaging_type || ''}</td>
                        <td>${product.quantity || ''} KG</td>
                        <td>
                            <button onclick="editPerishableProduct('${product.product_id}')" class="btn-edit">Edit</button>
                            <button onclick="deletePerishableProduct('${product.product_id}')" class="btn-delete">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.message.includes('table does not exist')) {
                    // Show setup notice
                    const setupNotice = document.getElementById('setupNotice');
                    if (setupNotice) {
                        setupNotice.style.display = 'block';
                    }
                    alert('Database table not found. Please run the database setup first.\n\nOptions:\n1. Click the "Setup Database" button above\n2. Visit debug_perishable.html and click "Create Table"');
                    perishableProductsTable.innerHTML = '<tr><td colspan="7">Table not found - Please setup database first</td></tr>';
                } else {
                    alert('Error loading perishable products: ' + error.message + '\n\nPlease check:\n1. XAMPP MySQL is running\n2. Database schema has been updated\n3. Check browser console for details');
                    perishableProductsTable.innerHTML = '<tr><td colspan="7">Error loading products - check console</td></tr>';
                }
            });
    }

    // Format status for display
    function formatStatus(status) {
        const statusMap = {
            'available': 'Available',
            'limited': 'Limited Stock',
            'out_of_stock': 'Out of Stock',
            'discontinued': 'Discontinued'
        };
        return statusMap[status] || status;
    }

    // Make functions globally available
    window.loadPerishableProducts = loadPerishableProducts;
    
    // Check table status function
    function checkTableStatus() {
        fetch('test_table.php')
            .then(response => response.json())
            .then(data => {
                const setupNotice = document.getElementById('setupNotice');
                if (!data.success || !data.table_exists) {
                    if (setupNotice) {
                        setupNotice.style.display = 'block';
                    }
                } else {
                    if (setupNotice) {
                        setupNotice.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.log('Table status check failed:', error.message);
            });
    }
});

// Create table directly function
function createTableDirectly() {
    const statusSpan = document.getElementById('createStatus');
    statusSpan.innerHTML = '<span style="color: blue;">Creating table...</span>';
    
    fetch('create_table_direct.php')
        .then(response => response.text())
        .then(data => {
            if (data.includes('SUCCESS') || data.includes('✓')) {
                statusSpan.innerHTML = '<span style="color: green;">✓ Table created!</span>';
                // Hide the notice
                const setupNotice = document.getElementById('setupNotice');
                if (setupNotice) {
                    setupNotice.style.display = 'none';
                }
                // Reload the products
                setTimeout(() => {
                    window.loadPerishableProducts();
                }, 1000);
            } else {
                statusSpan.innerHTML = '<span style="color: red;">Error: Check console</span>';
                console.log('Table creation response:', data);
            }
        })
        .catch(error => {
            statusSpan.innerHTML = '<span style="color: red;">Failed: ' + error.message + '</span>';
            console.error('Table creation error:', error);
        });
}

// Edit perishable product function
function editPerishableProduct(productId) {
    fetch(`php/get_perishable_product.php?id=${productId}`)
        .then(response => response.json())
        .then(response => {
            const product = response.success !== undefined ? response.data : response;
            if (!product || !product.product_id) {
                throw new Error('Product not found');
            }
            
            // Populate form fields
            document.getElementById('productId').value = product.product_id || '';
            document.getElementById('productName').value = product.product_name || '';
            document.getElementById('category').value = product.category || '';
            document.getElementById('shelfLifeDays').value = product.shelf_life_days || '';
            document.getElementById('packagingType').value = product.packaging_type || '';
            document.getElementById('quantity').value = product.quantity || '';
            
            // Scroll to form
            document.getElementById('perishableProductForm').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading product details: ' + error.message);
        });
}

// Delete perishable product function
function deletePerishableProduct(productId) {
    if (confirm('Are you sure you want to delete this perishable product?')) {
        // Find and temporarily hide the row for immediate visual feedback
        const tableRows = document.querySelectorAll('#perishableProductsTable tbody tr');
        let rowToRemove = null;
        
        tableRows.forEach(row => {
            const firstCell = row.cells[0];
            if (firstCell && firstCell.textContent.trim() === productId) {
                rowToRemove = row;
                // Add visual indication that deletion is in progress
                row.style.opacity = '0.5';
                row.style.backgroundColor = '#ffebee';
            }
        });
        
        fetch(`php/delete_perishable_product.php?id=${productId}`, {
            method: 'DELETE'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error('Server returned HTML instead of JSON. Response: ' + text.substring(0, 200));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove the row immediately for better user experience
                if (rowToRemove) {
                    rowToRemove.remove();
                }
                
                // Check if table is now empty
                const remainingRows = document.querySelectorAll('#perishableProductsTable tbody tr');
                if (remainingRows.length === 0) {
                    const tbody = document.getElementById('perishableProductsTable').getElementsByTagName('tbody')[0];
                    tbody.innerHTML = '<tr><td colspan="7">No perishable products found</td></tr>';
                }
                
                alert('Perishable product deleted successfully!');
            } else {
                // Restore row appearance if deletion failed
                if (rowToRemove) {
                    rowToRemove.style.opacity = '1';
                    rowToRemove.style.backgroundColor = '';
                }
                throw new Error(data.error || 'Failed to delete product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Restore row appearance on error
            if (rowToRemove) {
                rowToRemove.style.opacity = '1';
                rowToRemove.style.backgroundColor = '';
            }
            alert('Error deleting product: ' + error.message + '\n\nPlease check:\n1. XAMPP MySQL is running\n2. Database connection is working\n3. Check browser console for details');
        });
    }
}