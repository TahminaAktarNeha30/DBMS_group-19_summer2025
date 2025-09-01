#!/bin/bash

# Database Update Script
# This script updates the database schema to match frontend forms

echo \"Updating database schema...\"

# Run the updated database schema
mysql -u root -p agri_supply_chain < database_fixed.sql

echo \"Database schema updated successfully!\"
echo \"Please restart your web server and test the application.\"