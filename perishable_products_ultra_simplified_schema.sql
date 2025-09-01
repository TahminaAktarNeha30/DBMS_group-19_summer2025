-- Ultra Simplified Perishable Products Module SQL Schema
-- Focuses on: name, category, shelf life, packaging details, quantity, and supplier name

USE agri_supply_chain;

-- Ultra Simplified Perishable Products table with only essential fields
CREATE TABLE IF NOT EXISTS perishable_products (
    product_id VARCHAR(50) PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    
    -- Shelf Life Information
    shelf_life_days INT NOT NULL COMMENT 'Shelf life in days',
    
    -- Packaging Details
    packaging_type VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL COMMENT 'Quantity in kilograms',
    
    -- Supplier Information (simplified to just name)
    supplier_name VARCHAR(100) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Check Constraints for data integrity
    CHECK (shelf_life_days > 0),
    CHECK (quantity > 0)
);

-- Create indexes for better performance
CREATE INDEX idx_perishable_category ON perishable_products(category);
CREATE INDEX idx_perishable_shelf_life ON perishable_products(shelf_life_days);
CREATE INDEX idx_perishable_supplier ON perishable_products(supplier_name);

-- Insert sample perishable products
INSERT INTO perishable_products (
    product_id, product_name, category, 
    shelf_life_days,
    packaging_type, quantity,
    supplier_name
) VALUES
('PROD001', 'Organic Tomatoes', 'Vegetables', 
 7,
 'Plastic Crates', 5.0,
 'Fresh Farm Suppliers'),

('PROD002', 'Fresh Strawberries', 'Fruits',
 3,
 'Plastic Containers', 2.5,
 'Organic Harvest Co.'),

('PROD003', 'Leafy Spinach', 'Leafy Greens',
 5,
 'Plastic Bags', 1.5,
 'Organic Harvest Co.'),

('PROD004', 'Bell Peppers', 'Vegetables',
 10,
 'Cardboard Boxes', 8.0,
 'Premium Produce Ltd.'),

('PROD005', 'Fresh Herbs Mix', 'Herbs',
 4,
 'Plastic Containers', 0.5,
 'Fresh Farm Suppliers'),

('PROD006', 'Carrots', 'Root Vegetables',
 14,
 'Mesh Bags', 10.0,
 'Premium Produce Ltd.'),

('PROD007', 'Lettuce', 'Leafy Greens',
 7,
 'Plastic Bags', 2.0,
 'Fresh Farm Suppliers'),

('PROD008', 'Apples', 'Fruits',
 21,
 'Cardboard Boxes', 15.0,
 'Premium Produce Ltd.');