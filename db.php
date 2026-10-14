<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$password = "";
$database = "restaurant_db";

try {
    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");

    // Billing tables are created if they do not exist. Existing menu_items data is preserved.
    $conn->query("CREATE TABLE IF NOT EXISTS bills (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
        tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        grand_total DECIMAL(10,2) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $conn->query("CREATE TABLE IF NOT EXISTS bill_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        bill_id INT UNSIGNED NOT NULL,
        menu_item_id INT UNSIGNED NULL,
        item_name VARCHAR(150) NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        line_total DECIMAL(10,2) NOT NULL,
        INDEX (bill_id),
        CONSTRAINT fk_bill_items_bill FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Add demo menu items only when the menu is empty; existing menu data is preserved.
    $menuCount = (int)$conn->query("SELECT COUNT(*) AS total FROM menu_items")->fetch_assoc()["total"];
    if ($menuCount === 0) {
        $conn->query("INSERT INTO menu_items (item_name, category, price) VALUES
            ('Idli (2 pcs)', 'Tiffins', 35.00),
            ('Plain Dosa', 'Tiffins', 50.00),
            ('Masala Dosa', 'Tiffins', 70.00),
            ('Poori (2 pcs)', 'Tiffins', 55.00),
            ('Veg Fried Rice', 'Fast Food', 110.00),
            ('Veg Sandwich', 'Fast Food', 80.00),
            ('French Fries', 'Fast Food', 75.00),
            ('Veg Burger', 'Fast Food', 95.00),
            ('Tea', 'Coffee', 20.00),
            ('Filter Coffee', 'Coffee', 35.00),
            ('Cold Coffee', 'Coffee', 90.00)");
    }
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit("Database connection failed. Check that MySQL is running and restaurant_db exists. Details: " . htmlspecialchars($e->getMessage()));
}
?>