-- Simplified Perishable Products Module SQL Schema
-- Focuses on: name, category, storage requirements, shelf life, packaging details, and supplier information

USE agri_supply_chain;

-- First, create suppliers table for foreign key reference
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id VARCHAR(50) PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    contact_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    registration_date DATE DEFAULT (CURRENT_DATE),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Simplified Perishable Products table with only essential fields
CREATE TABLE IF NOT EXISTS perishable_products (
    product_id VARCHAR(50) PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    
    -- Storage Requirements
    min_temperature DECIMAL(5,2) NOT NULL COMMENT 'Minimum storage temperature in Celsius',
    max_temperature DECIMAL(5,2) NOT NULL COMMENT 'Maximum storage temperature in Celsius',
    min_humidity DECIMAL(5,2) NOT NULL COMMENT 'Minimum humidity percentage',
    max_humidity DECIMAL(5,2) NOT NULL COMMENT 'Maximum humidity percentage',
    storage_conditions TEXT COMMENT 'Additional storage requirements',
    
    -- Shelf Life Information
    shelf_life_days INT NOT NULL COMMENT 'Shelf life in days',
    expiry_warning_days INT DEFAULT 3 COMMENT 'Days before expiry to show warning',
    
    -- Packaging Details
    packaging_type VARCHAR(50) NOT NULL,
    package_weight DECIMAL(10,2) COMMENT 'Weight per package in grams',
    
    -- Supplier Information
    supplier_id VARCHAR(50) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Key Constraints
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Check Constraints for data integrity
    CHECK (min_temperature <= max_temperature),
    CHECK (min_humidity <= max_humidity),
    CHECK (shelf_life_days > 0),
    CHECK (package_weight > 0)
);

-- Create indexes for better performance
CREATE INDEX idx_perishable_category ON perishable_products(category);
CREATE INDEX idx_perishable_supplier ON perishable_products(supplier_id);
CREATE INDEX idx_perishable_shelf_life ON perishable_products(shelf_life_days);

-- Insert sample data for suppliers
INSERT INTO suppliers (supplier_id, supplier_name, contact_person, contact_number, email, address) VALUES
('SUP001', 'Fresh Farm Suppliers', 'John Smith', '555-0101', 'john@freshfarm.com', '123 Farm Road, Agricultural District'),
('SUP002', 'Organic Harvest Co.', 'Sarah Johnson', '555-0102', 'sarah@organicharvest.com', '456 Green Valley, Organic Zone'),
('SUP003', 'Premium Produce Ltd.', 'Michael Brown', '555-0103', 'mike@premiumproduce.com', '789 Quality Street, Business Park');

-- Insert sample perishable products
INSERT INTO perishable_products (
    product_id, product_name, category, 
    min_temperature, max_temperature, min_humidity, max_humidity, storage_conditions,
    shelf_life_days, expiry_warning_days,
    packaging_type, package_weight,
    supplier_id
) VALUES
('PROD001', 'Organic Tomatoes', 'Vegetables', 
 10.0, 13.0, 85.0, 95.0, 'Store in ventilated area, avoid direct sunlight',
 7, 2,
 'Plastic Crates', 500.0,
 'SUP002'),

('PROD002', 'Fresh Strawberries', 'Fruits',
 0.0, 4.0, 90.0, 95.0, 'Refrigerate immediately, high humidity required',
 3, 1,
 'Plastic Containers', 250.0,
 'SUP001'),

('PROD003', 'Leafy Spinach', 'Leafy Greens',
 1.0, 4.0, 95.0, 98.0, 'High humidity, minimal air circulation',
 5, 2,
 'Plastic Bags', 200.0,
 'SUP002'),

('PROD004', 'Bell Peppers', 'Vegetables',
 7.0, 10.0, 85.0, 90.0, 'Cool storage, avoid moisture buildup',
 10, 3,
 'Cardboard Boxes', 1000.0,
 'SUP003'),

('PROD005', 'Fresh Herbs Mix', 'Herbs',
 2.0, 5.0, 90.0, 95.0, 'High humidity, minimal light exposure',
 4, 1,
 'Plastic Containers', 100.0,
 'SUP001');