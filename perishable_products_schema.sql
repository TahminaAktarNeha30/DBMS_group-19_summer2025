-- Perishable Products Module SQL Schema
-- This module manages detailed data on perishable products including storage requirements and supplier information

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

-- Perishable Products table with comprehensive details
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
    packaging_material VARCHAR(100),
    package_weight DECIMAL(10,2) COMMENT 'Weight per package in grams',
    package_dimensions VARCHAR(100) COMMENT 'Length x Width x Height in cm',
    packages_per_unit INT DEFAULT 1,
    
    -- Supplier Information
    supplier_id VARCHAR(50) NOT NULL,
    supplier_product_code VARCHAR(100),
    
    -- Additional Product Details
    origin_country VARCHAR(50),
    organic_certified BOOLEAN DEFAULT FALSE,
    nutritional_info TEXT,
    handling_instructions TEXT,
    quality_grade VARCHAR(20),
    
    -- Pricing and Availability
    unit_price DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'USD',
    availability_status VARCHAR(20) DEFAULT 'available',
    
    -- Tracking and Compliance
    product_code VARCHAR(100) UNIQUE,
    barcode VARCHAR(100),
    certification_details TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Key Constraints
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Check Constraints for data integrity
    CHECK (min_temperature <= max_temperature),
    CHECK (min_humidity <= max_humidity),
    CHECK (shelf_life_days > 0),
    CHECK (package_weight > 0),
    CHECK (unit_price >= 0)
);

-- Create indexes for better performance
CREATE INDEX idx_perishable_category ON perishable_products(category);
CREATE INDEX idx_perishable_supplier ON perishable_products(supplier_id);
CREATE INDEX idx_perishable_shelf_life ON perishable_products(shelf_life_days);
CREATE INDEX idx_perishable_status ON perishable_products(availability_status);

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
    packaging_type, packaging_material, package_weight, package_dimensions, packages_per_unit,
    supplier_id, supplier_product_code,
    origin_country, organic_certified, nutritional_info, handling_instructions, quality_grade,
    unit_price, currency, availability_status,
    product_code, barcode, certification_details
) VALUES
('PROD001', 'Organic Tomatoes', 'Vegetables', 
 10.0, 13.0, 85.0, 95.0, 'Store in ventilated area, avoid direct sunlight',
 7, 2,
 'Plastic Crates', 'Food-grade plastic', 500.0, '30x20x15', 20,
 'SUP002', 'ORG-TOM-001',
 'USA', TRUE, 'Rich in lycopene and vitamin C', 'Handle gently, do not stack more than 5 crates high', 'Grade A',
 2.50, 'USD', 'available',
 'PT-ORG-001', '1234567890123', 'USDA Organic Certified'),

('PROD002', 'Fresh Strawberries', 'Fruits',
 0.0, 4.0, 90.0, 95.0, 'Refrigerate immediately, high humidity required',
 3, 1,
 'Plastic Containers', 'Food-grade plastic with ventilation', 250.0, '15x10x8', 24,
 'SUP001', 'FRESH-STR-001',
 'USA', FALSE, 'High in vitamin C and antioxidants', 'Keep refrigerated, do not wash until ready to use', 'Premium',
 4.99, 'USD', 'available',
 'ST-FRESH-002', '2345678901234', 'FDA Approved'),

('PROD003', 'Leafy Spinach', 'Leafy Greens',
 1.0, 4.0, 95.0, 98.0, 'High humidity, minimal air circulation',
 5, 2,
 'Plastic Bags', 'Perforated plastic bags', 200.0, '25x15x5', 50,
 'SUP002', 'ORG-SPN-001',
 'USA', TRUE, 'High in iron, folate, and vitamins A, C, K', 'Mist lightly, avoid crushing leaves', 'Grade A',
 1.99, 'USD', 'available',
 'SP-ORG-003', '3456789012345', 'USDA Organic Certified');