<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$rawCart = $_POST["cart_json"] ?? "";
$cart = json_decode($rawCart, true);
if (!is_array($cart) || !$cart) {
    header("Location: index.php?error=empty");
    exit;
}

$requested = [];
foreach ($cart as $key => $item) {
    $id = filter_var($item["id"] ?? $key, FILTER_VALIDATE_INT);
    $qty = filter_var($item["qty"] ?? 0, FILTER_VALIDATE_INT);
    if ($id && $id > 0 && $qty && $qty > 0 && $qty <= 99) $requested[$id] = $qty;
}
if (!$requested) {
    header("Location: index.php?error=empty");
    exit;
}

$ids = array_keys($requested);
$placeholders = implode(",", array_fill(0, count($ids), "?"));
$types = str_repeat("i", count($ids));
$stmt = $conn->prepare("SELECT id, item_name, category, price FROM menu_items WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $qty = $requested[(int)$row["id"]] ?? 0;
    if ($qty < 1) continue;
    $price = (float)$row["price"];
    $lineTotal = $price * $qty;
    $items[] = ["id" => (int)$row["id"], "name" => $row["item_name"], "price" => $price, "qty" => $qty, "total" => $lineTotal];
    $subtotal += $lineTotal;
}
$stmt->close();

if (!$items) {
    header("Location: index.php?error=empty");
    exit;
}

$tax = round($subtotal * 0.05, 2);
$grandTotal = $subtotal + $tax;

try {
    $conn->begin_transaction();
    $billStmt = $conn->prepare("INSERT INTO bills (subtotal, tax_amount, grand_total) VALUES (?, ?, ?)");
    $billStmt->bind_param("ddd", $subtotal, $tax, $grandTotal);
    $billStmt->execute();
    $billId = $conn->insert_id;
    $billStmt->close();

    $lineStmt = $conn->prepare("INSERT INTO bill_items (bill_id, menu_item_id, item_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $menuId = $item["id"]; $name = $item["name"]; $price = $item["price"]; $qty = $item["qty"]; $lineTotal = $item["total"];
        $lineStmt->bind_param("iisdid", $billId, $menuId, $name, $price, $qty, $lineTotal);
        $lineStmt->execute();
    }
    $lineStmt->close();
    $conn->commit();
    header("Location: print_bill.php?id=" . $billId);
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    exit("Unable to generate bill. Please check your database tables. " . htmlspecialchars($e->getMessage()));
}
?>