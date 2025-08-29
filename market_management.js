document.addEventListener('DOMContentLoaded', function() {
    const marketForm = document.getElementById('marketForm');
    const marketTable = document.getElementById('marketTable').getElementsByTagName('tbody')[0];

    // Load existing markets
    loadMarkets();

    // Form submission handler
    marketForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            marketId: document.getElementById('marketId').value,
            name: document.getElementById('name').value,
            type: document.getElementById('type').value,
            location: document.getElementById('location').value,
            contactPerson: document.getElementById('contactPerson').value,
            operationalHours: document.getElementById('operationalHours').value,
            quantity: document.getElementById('quantity').value
        };

        // Send data to PHP backend
        fetch('php/save_market.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Market saved successfully!');
                const newRow = marketTable.insertRow();
                newRow.innerHTML = `
                        <td>${formData.marketId}</td>
                        <td>${formData.name}</td>
                        <td>${formatType(formData.type)}</td>
                        <td>${formData.location}</td>
                        <td>${formData.contactPerson}</td>
                        <td>${formData.operationalHours}</td>
                        <td>${formData.quantity}</td>
                        <td>
                            <button onclick="editMarket('${formData.marketId}')">Edit</button>
                            <button onclick="deleteMarket('${formData.marketId}')">Delete</button>
                        </td>
                    `;
                marketForm.reset();
            } else {
                alert('Error saving market: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving market. Please try again.');
        });
    });

    // Load markets from database
    function loadMarkets() {
        fetch('php/get_markets.php')
            .then(response => response.json())
            .then(data => {
                marketTable.innerHTML = '';
                data.forEach(market => {
                    const row = marketTable.insertRow();
                    row.innerHTML = `
                        <td>${market.market_id}</td>
                        <td>${market.name}</td>
                        <td>${formatType(market.type)}</td>
                        <td>${market.location}</td>
                        <td>${market.contact_person}</td>
                        <td>${market.operational_hours}</td>
                        <td>${market.quantity}</td>
                        <td>
                            <button onclick="editMarket('${market.market_id}')">Edit</button>
                            <button onclick="deleteMarket('${market.market_id}')">Delete</button>
                        </td>
                    `;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading markets. Please try again.');
            });
    }
});

// Format market type for display
function formatType(type) {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Edit market function
function editMarket(marketId) {
    fetch(`php/get_market.php?id=${marketId}`)
        .then(response => response.json())
        .then(market => {
            document.getElementById('marketId').value = market.market_id;
            document.getElementById('name').value = market.name;
            document.getElementById('type').value = market.type;
            document.getElementById('location').value = market.location;
            document.getElementById('contactPerson').value = market.contact_person;
            document.getElementById('operationalHours').value = market.operational_hours;
            document.getElementById('quantity').value = market.quantity;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading market details. Please try again.');
        });
}

// Delete market function
function deleteMarket(marketId) {
    if (confirm('Are you sure you want to delete this market?')) {
        fetch(`php/delete_market.php?id=${marketId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Market deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting market: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting market. Please try again.');
        });
    }
}
