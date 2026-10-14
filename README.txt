JEEVZ — PHP + MYSQL + XAMPP
======================================

INCLUDED FILES
--------------
index.php        Aesthetic menu, category filters, search, cart and totals
manage_menu.php  Add and remove menu items (Tiffins, Fast Food, Coffee)
generate_bill.php Validates the cart, calculates GST, and saves the bill
print_bill.php   Restaurant-style printable receipt
bill_history.php Past bills and print links
db.php           MySQL connection and billing table setup
style.css        Responsive warm cream, orange and brown design
database.sql     Safe CREATE TABLE IF NOT EXISTS setup

INSTALLATION
------------
1. Extract this ZIP.
2. Copy the Jeevz folder to:
   C:\xampp\htdocs\
3. Open XAMPP Control Panel and start Apache and MySQL.
4. Open http://localhost/phpmyadmin
5. Select the SQL tab. Open database.sql in a text editor, copy the SQL,
   paste it into phpMyAdmin, and click Go.
6. If your restaurant_db and menu_items table already exist, the script
   does not drop or overwrite them. Confirm that the menu_items columns
   are named id, item_name, category, and price.
7. Open:
   http://localhost/Jeevz/

DATABASE EXPECTATIONS
---------------------
Database: restaurant_db
Existing menu table: menu_items
Expected columns: id, item_name, category, price
Expected category values: Tiffins, Fast Food, Coffee

The app creates bills and bill_items tables if they are missing.
It uses the default XAMPP database account root with a blank password.
If your MySQL account uses a password, update db.php.

FEATURES
--------
- Responsive aesthetic interface without external food images
- Category filter and menu search
- Add to cart, quantity controls, remove items, clear order
- Subtotal, illustrative GST at 5%, and grand total
- Saved bill history and restaurant-style printable receipt
- Add/remove menu items

NOTES
-----
- GST is set to 5% as a project example; verify the applicable tax rate
  before using this for real transactions.
- This is a local learning project. Before publishing it online, add
  staff authentication, CSRF protection, and stronger access controls.
- The delete-menu action is simplified for a classroom demo; use POST
  with CSRF protection in a public deployment.
- The project assumes the menu_items table columns are id, item_name,
  category, price. If your existing table uses different column names,
  update the SQL queries accordingly.
