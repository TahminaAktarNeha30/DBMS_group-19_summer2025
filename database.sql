CREATE DATABASE IF NOT EXISTS agri_supply_chain;
USE agri_supply_chain;

-- Product Batch Management
CREATE TABLE IF NOT EXISTS product_batches (
    batch_id VARCHAR(50) PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    production_date DATE NOT NULL,
    expiry_date DATE,
    quality_grade VARCHAR(10),
    weight DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Package Management
CREATE TABLE IF NOT EXISTS packages (
    package_id VARCHAR(50) PRIMARY KEY,
    batch_id VARCHAR(50),
    package_type VARCHAR(50) NOT NULL,
    weight DECIMAL(10,2),
    packaging_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES product_batches(batch_id)
);

-- Factory Management
CREATE TABLE IF NOT EXISTS factories (
    factory_id VARCHAR(50) PRIMARY KEY,
    factory_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT,
    status VARCHAR(20)
);

-- Storage Facility Management
CREATE TABLE IF NOT EXISTS storage_facilities (
    facility_id VARCHAR(50) PRIMARY KEY,
    facility_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT,
    temperature DECIMAL(5,2),
    humidity DECIMAL(5,2)
);

-- Inventory Rotation Management
CREATE TABLE IF NOT EXISTS inventory_rotations (
    rotation_id VARCHAR(50) PRIMARY KEY,
    batch_id VARCHAR(50),
    facility_id VARCHAR(50),
    quantity INT,
    strategy VARCHAR(50),
    rotation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rotation_type VARCHAR(50),
    FOREIGN KEY (batch_id) REFERENCES product_batches(batch_id),
    FOREIGN KEY (facility_id) REFERENCES storage_facilities(facility_id)
);

-- Sensor Data Monitoring
CREATE TABLE IF NOT EXISTS sensor_data (
    sensor_id VARCHAR(50),
    facility_id VARCHAR(50),
    temperature DECIMAL(5,2),
    humidity DECIMAL(5,2),
    reading_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (sensor_id, reading_time),
    FOREIGN KEY (facility_id) REFERENCES storage_facilities(facility_id)
);

-- Market Management
CREATE TABLE IF NOT EXISTS markets (
    market_id VARCHAR(50) PRIMARY KEY,
    market_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    market_type VARCHAR(50),
    contact_info VARCHAR(100)
);

-- Farmer Information
CREATE TABLE IF NOT EXISTS farmers (
    farmer_id VARCHAR(50) PRIMARY KEY,
    farmer_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    address VARCHAR(255),
    registration_date DATE
);

-- Farm Management
CREATE TABLE IF NOT EXISTS farms (
    farm_id VARCHAR(50) PRIMARY KEY,
    farmer_id VARCHAR(50),
    farm_name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    total_area DECIMAL(10,2),
    soil_type VARCHAR(50),
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id)
);

-- Crop Management
CREATE TABLE IF NOT EXISTS crops (
    crop_id VARCHAR(50) PRIMARY KEY,
    farm_id VARCHAR(50),
    crop_name VARCHAR(100) NOT NULL,
    planting_date DATE,
    expected_harvest_date DATE,
    actual_harvest_date DATE,
    status VARCHAR(50),
    FOREIGN KEY (farm_id) REFERENCES farms(farm_id)
);

-- Harvest Record Management
CREATE TABLE IF NOT EXISTS harvest_records (
    record_id VARCHAR(50) PRIMARY KEY,
    crop_id VARCHAR(50),
    status VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    harvest_time DATETIME NOT NULL,
    sowing_date DATE NOT NULL,
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id)
);

-- Spoilage Record Management
CREATE TABLE IF NOT EXISTS spoilage_records (
    record_id VARCHAR(50) PRIMARY KEY,
    source_type VARCHAR(50) NOT NULL,
    quantity_lost DECIMAL(10,2) NOT NULL,
    reason VARCHAR(255),
    disposal_method VARCHAR(50) NOT NULL,
    recycling_output VARCHAR(255),
    financial_loss DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
