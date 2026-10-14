-- Jeevz
-- Your existing restaurant_db.menu_items table is not dropped or overwritten.
-- Run this in phpMyAdmin only if the billing tables do not already exist.

CREATE DATABASE IF NOT EXISTS restaurant_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE restaurant_db;

-- If you already have menu_items, keep your existing table and its data.
-- This creates a basic menu_items table only when it does not exist.
CREATE TABLE IF NOT EXISTS menu_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bill_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_id INT UNSIGNED NOT NULL,
    menu_item_id INT UNSIGNED NULL,
    item_name VARCHAR(150) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    INDEX (bill_id),
    CONSTRAINT fk_bill_items_bill FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Optional sample items. Uncomment these INSERTs only if your menu is empty.
-- INSERT INTO menu_items (item_name, category, price) VALUES
-- ('Idli', 'Tiffins', 35.00),
-- ('Masala Dosa', 'Tiffins', 70.00),
-- ('Veg Sandwich', 'Fast Food', 80.00),
-- ('Cold Coffee', 'Coffee', 90.00);
