-- Updated Database Schema to Match Frontend Forms
CREATE DATABASE IF NOT EXISTS agri_supply_chain;
USE agri_supply_chain;

-- Package Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS packages (
    package_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100),
    packaging_info TEXT,
    type VARCHAR(50) NOT NULL,
    weight DECIMAL(10,2) NOT NULL,
    production_date DATE,
    expiration_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product Batch Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS product_batches (
    batch_id VARCHAR(50) PRIMARY KEY,
    package_count INT NOT NULL,
    batch_weight DECIMAL(10,2) NOT NULL,
    quality_status VARCHAR(50) NOT NULL,
    creation_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crop Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS crops (
    crop_id VARCHAR(50) PRIMARY KEY,
    crop_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    water_requirements VARCHAR(50),
    soil_preference VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Farm Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS farms (
    farm_id VARCHAR(50) PRIMARY KEY,
    location VARCHAR(255) NOT NULL,
    size DECIMAL(10,2) NOT NULL,
    soil_type VARCHAR(50) NOT NULL,
    irrigation_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Factory Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS factories (
    factory_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    processing_capacity INT NOT NULL,
    capacity_unit VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Market Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS markets (
    market_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100),
    operational_hours VARCHAR(50),
    quantity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Farmer Information (Updated to match frontend)
CREATE TABLE IF NOT EXISTS farmers (
    farmer_id VARCHAR(50) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Storage Facility Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS storage_facilities (
    storage_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    status VARCHAR(20),
    entry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inventory Rotation Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS inventory_rotations (
    rotation_id VARCHAR(50) PRIMARY KEY,
    strategy VARCHAR(50) NOT NULL,
    rotation_date DATE NOT NULL,
    rotation_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sensor Data Monitoring (Updated to match frontend)
CREATE TABLE IF NOT EXISTS sensor_data (
    sensor_id VARCHAR(50) NOT NULL,
    humidity DECIMAL(5,2) NOT NULL,
    oxygen_level DECIMAL(5,2) NOT NULL,
    ph_level DECIMAL(5,2) NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    reading_timestamp DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (sensor_id, reading_timestamp)
);

-- Pre-Harvest Material Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS pre_harvest_materials (
    pre_harvest_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    harvesting_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Harvest Record Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS harvest_records (
    record_id VARCHAR(50) PRIMARY KEY,
    status VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    harvest_time DATETIME NOT NULL,
    sowing_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Spoilage Record Management (Updated to match frontend)
CREATE TABLE IF NOT EXISTS spoilage_records (
    record_id VARCHAR(50) PRIMARY KEY,
    source_type VARCHAR(50) NOT NULL,
    quantity_lost DECIMAL(10,2) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    disposal_method VARCHAR(50) NOT NULL,
    recycling_output VARCHAR(255),
    financial_loss DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);