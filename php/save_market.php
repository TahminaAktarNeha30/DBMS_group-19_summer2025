<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

try {
    // Database connection
    $db_config = include 'config.php';
    if (!$db_config) {
        throw new Exception('Config file not found');
    }
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required data
    if (!$data || !isset($data['marketId']) || !isset($data['name']) || !isset($data['type']) || !isset($data['location'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields: marketId, name, type, location']);
        exit;
    }
    
    // Check table structure to determine available columns
    $columnsResult = $conn->query("SHOW COLUMNS FROM markets");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Prepare variables for binding (bind_param requires references)
    $marketId = $data['marketId'];
    $name = $data['name'];
    $type = $data['type'];
    $location = $data['location'];
    $contactPerson = isset($data['contactPerson']) ? $data['contactPerson'] : null;
    $operationalHours = isset($data['operationalHours']) ? $data['operationalHours'] : null;
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : null;
    
    // Build dynamic query based on available columns
    $insertColumns = ['market_id'];
    $insertValues = ['?'];
    $updateColumns = [];
    $bindTypes = 's';
    $bindValues = [&$marketId];
    
    // Handle name column (market_name vs name)
    if (in_array('market_name', $availableColumns)) {
        $insertColumns[] = 'market_name';
        $insertValues[] = '?';
        $updateColumns[] = 'market_name = VALUES(market_name)';
        $bindTypes .= 's';
        $bindValues[] = &$name;
    } else if (in_array('name', $availableColumns)) {
        $insertColumns[] = 'name';
        $insertValues[] = '?';
        $updateColumns[] = 'name = VALUES(name)';
        $bindTypes .= 's';
        $bindValues[] = &$name;
    }
    
    // Handle type column (market_type vs type)
    if (in_array('market_type', $availableColumns)) {
        $insertColumns[] = 'market_type';
        $insertValues[] = '?';
        $updateColumns[] = 'market_type = VALUES(market_type)';
        $bindTypes .= 's';
        $bindValues[] = &$type;
    } else if (in_array('type', $availableColumns)) {
        $insertColumns[] = 'type';
        $insertValues[] = '?';
        $updateColumns[] = 'type = VALUES(type)';
        $bindTypes .= 's';
        $bindValues[] = &$type;
    }
    
    // Add location
    if (in_array('location', $availableColumns)) {
        $insertColumns[] = 'location';
        $insertValues[] = '?';
        $updateColumns[] = 'location = VALUES(location)';
        $bindTypes .= 's';
        $bindValues[] = &$location;
    }
    
    // Handle contact person column (contact_info vs contact_person)
    if (in_array('contact_info', $availableColumns) && $contactPerson !== null) {
        $insertColumns[] = 'contact_info';
        $insertValues[] = '?';
        $updateColumns[] = 'contact_info = VALUES(contact_info)';
        $bindTypes .= 's';
        $bindValues[] = &$contactPerson;
    } else if (in_array('contact_person', $availableColumns) && $contactPerson !== null) {
        $insertColumns[] = 'contact_person';
        $insertValues[] = '?';
        $updateColumns[] = 'contact_person = VALUES(contact_person)';
        $bindTypes .= 's';
        $bindValues[] = &$contactPerson;
    }
    
    // Add operational hours if column exists
    if (in_array('operational_hours', $availableColumns) && $operationalHours !== null) {
        $insertColumns[] = 'operational_hours';
        $insertValues[] = '?';
        $updateColumns[] = 'operational_hours = VALUES(operational_hours)';
        $bindTypes .= 's';
        $bindValues[] = &$operationalHours;
    }
    
    // Add quantity if column exists
    if (in_array('quantity', $availableColumns) && $quantity !== null) {
        $insertColumns[] = 'quantity';
        $insertValues[] = '?';
        $updateColumns[] = 'quantity = VALUES(quantity)';
        $bindTypes .= 'i';
        $bindValues[] = &$quantity;
    }
    
    // Build the complete query
    $query = "INSERT INTO markets (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertValues) . ")";
    if (!empty($updateColumns)) {
        $query .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updateColumns);
    }
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Bind parameters dynamically
    if (!empty($bindValues)) {
        $stmt->bind_param($bindTypes, ...$bindValues);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Market saved successfully']);
    } else {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
