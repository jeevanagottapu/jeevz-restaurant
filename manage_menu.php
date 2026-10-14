<?php
require_once "db.php";
$message = ""; $error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_item"])) {
    $name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = filter_var($_POST["price"] ?? "", FILTER_VALIDATE_FLOAT);
    if ($name === "" || !in_array($category, ["Tiffins", "Fast Food", "Coffee"], true) || $price === false || $price < 0) {
        $error = "Enter a valid item name, category, and price.";
    } else {
        $stmt = $conn->prepare("INSERT INTO menu_items (item_name, category, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $category, $price);
        $stmt->execute(); $stmt->close();
        header("Location: manage_menu.php?added=1"); exit;
    }
}
if (isset($_GET["delete"])) {
    $id = filter_input(INPUT_GET, "delete", FILTER_VALIDATE_INT);
    if ($id && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();
        header("Location: manage_menu.php?deleted=1"); exit;
    }
}
if (isset($_GET["added"])) $message = "Menu item added successfully.";
if (isset($_GET["deleted"])) $message = "Menu item deleted.";
$result = $conn->query("SELECT id, item_name, category, price FROM menu_items ORDER BY category, item_name");
$items = []; while ($row = $result->fetch_assoc()) $items[] = $row;
function money($amount) { return "₹" . number_format((float)$amount, 2); }
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Manage Menu | Jeevz</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header">
    <a class="brand" href="index.php"><span class="brand-icon">♨</span><span><strong>Jeevz</strong><small>GOOD FOOD • HAPPY MOOD</small></span></a>
    <nav class="main-nav"><a href="index.php">Menu & Billing</a><a class="nav-active" href="manage_menu.php">Manage Menu</a><a href="bill_history.php">Bill History</a></nav>
    <a class="header-pill" href="index.php">← Back to menu</a>
</header>
<main class="page-wrap history-page">
    <section class="history-hero"><p class="eyebrow">STAFF WORKSPACE</p><h1>Keep the menu <em>fresh.</em></h1><p>Add new tiffins, fast food, and coffee items to your restaurant menu.</p></section>
    <?php if ($message): ?><div class="flash-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <section class="manage-grid">
        <div class="manage-form-card"><p class="eyebrow dark-eyebrow">NEW DISH</p><h2>Add a menu item<span>.</span></h2><p class="section-subtitle">Add a dish and its price to your menu.</p>
            <form method="POST" class="menu-form">
                <label for="item_name">Item name</label><input id="item_name" name="item_name" placeholder="e.g. Masala Dosa" required>
                <label for="category">Category</label><select id="category" name="category" required><option value="">Choose category</option><option>Tiffins</option><option>Fast Food</option><option>Coffee</option></select>
                <label for="price">Price (₹)</label><input id="price" name="price" type="number" min="0" step="0.01" placeholder="e.g. 60.00" required>
                <button class="checkout-button" type="submit" name="add_item">Add to menu <span>→</span></button>
            </form>
        </div>
        <div class="history-panel menu-list-panel"><div class="history-heading"><div><h2>Current menu</h2><p><?= count($items) ?> items listed</p></div></div>
            <?php if (!$items): ?><div class="empty-menu"><h3>Your menu is empty</h3><p>Add your first item using the form.</p></div><?php else: ?>
            <div class="table-scroll"><table class="history-table"><thead><tr><th>ITEM</th><th>CATEGORY</th><th>PRICE</th><th>ACTION</th></tr></thead><tbody>
            <?php foreach ($items as $item): ?><tr><td><strong><?= htmlspecialchars($item["item_name"]) ?></strong></td><td><span class="category-badge"><?= htmlspecialchars($item["category"]) ?></span></td><td><strong><?= money($item["price"]) ?></strong></td><td><a class="delete-menu-link" href="manage_menu.php?delete=<?= (int)$item["id"] ?>" onclick="return confirm('Delete this menu item?')">Delete</a></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
        </div>
    </section>
    <footer class="site-footer"><span>© <?= date("Y") ?> Jeevz</span><span>MADE WITH CARE <b>♥</b></span></footer>
</main>
</body></html>