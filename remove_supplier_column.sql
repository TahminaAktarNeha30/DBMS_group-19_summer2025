-- Remove supplier_name column from perishable_products table
-- This script will update the existing table to remove supplier_name

USE agri_supply_chain;

-- First, drop the index on supplier_name if it exists
DROP INDEX IF EXISTS idx_perishable_supplier ON perishable_products;

-- Remove the supplier_name column
ALTER TABLE perishable_products 
DROP COLUMN supplier_name;

-- Verify the table structure
DESCRIBE perishable_products;