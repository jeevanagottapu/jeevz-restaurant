<?php
require_once "db.php";

$category = trim($_GET["category"] ?? "All");
$search = trim($_GET["search"] ?? "");
$allowedCategories = ["All", "Tiffins", "Fast Food", "Coffee"];
$categories = ["All", "Tiffins", "Fast Food", "Coffee"];

$menu = [];
$sql = "SELECT id, item_name, category, price FROM menu_items";
$conditions = [];
$params = [];
$types = "";

if ($category !== "All" && in_array($category, $allowedCategories, true)) {
    $conditions[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}
if ($search !== "") {
    $conditions[] = "item_name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}
if ($conditions) $sql .= " WHERE " . implode(" AND ", $conditions);
$sql .= " ORDER BY category, item_name";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $menu[] = $row;
$stmt->close();

$categoryCounts = ["All" => 0, "Tiffins" => 0, "Fast Food" => 0, "Coffee" => 0];
$countResult = $conn->query("SELECT category, COUNT(*) AS total FROM menu_items GROUP BY category");
$totalMenu = 0;
while ($row = $countResult->fetch_assoc()) {
    $count = (int)$row["total"];
    $totalMenu += $count;
    if (isset($categoryCounts[$row["category"]])) $categoryCounts[$row["category"]] = $count;
}
$categoryCounts["All"] = $totalMenu;

function money($amount) { return "₹" . number_format((float)$amount, 2); }
function categoryIcon($category) {
    return match ($category) {
        "Tiffins" => "☀",
        "Fast Food" => "✦",
        "Coffee" => "☕",
        default => "🍴"
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeevz | Menu & Billing</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">
        <span class="brand-icon">♨</span>
        <span><strong>Jeevz</strong><small>GOOD FOOD • HAPPY MOOD</small></span>
    </a>
    <nav class="main-nav">
        <a class="nav-active" href="index.php">Menu & Billing</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="bill_history.php">Bill History</a>
    </nav>
    <a class="header-pill" href="#order-panel">Your order <span id="header-cart-count">0</span></a>
</header>

<main class="page-wrap">
    <section class="hero">
        <div class="hero-content">
            <div class="eyebrow"><span></span> MADE FRESH, SERVED WITH LOVE</div>
            <h1>A little taste of<br><em>foodie heaven.</em></h1>
            <p>Good food, great moments. Find your favourites, build your order, and create a bill in seconds.</p>
            <a class="hero-button" href="#menu-section">Explore the menu <span>↓</span></a>
            <div class="hero-note"><span class="note-star">✦</span> Freshly prepared. Happily served.</div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="art-orbit orbit-a"></div><div class="art-orbit orbit-b"></div>
            <div class="plate"><div class="plate-inner"><span class="plate-spark">✦</span><span class="plate-leaf">❧</span><span class="plate-center">J</span><span class="plate-caption">JEEVZ</span></div></div>
            <div class="floating-note note-top">Fresh & tasty <span>♥</span></div>
            <div class="floating-note note-bottom"><span class="tiny-dot"></span> Made with care</div>
        </div>
        <div class="hero-bottom-line"><span>01 / OUR MENU</span><span>YOUR NEXT FAVOURITE IS HERE ↘</span></div>
    </section>

    <section class="menu-layout" id="menu-section">
        <div class="menu-column">
            <div class="section-heading">
                <div><p class="eyebrow dark-eyebrow">THE GOOD STUFF</p><h2>Explore our menu<span>.</span></h2><p class="section-subtitle">Simple cravings. Delicious choices.</p></div>
                <div class="menu-count"><strong><?= count($menu) ?></strong><span>items shown</span></div>
            </div>
            <div class="filter-row">
                <div class="category-tabs">
                    <?php foreach ($categories as $cat): ?>
                        <a class="category-tab <?= $category === $cat ? "selected" : "" ?>" href="?category=<?= urlencode($cat) ?><?= $search !== "" ? "&search=" . urlencode($search) : "" ?>">
                            <span><?= $cat === "All" ? "✦" : ($cat === "Tiffins" ? "☀" : ($cat === "Fast Food" ? "◈" : "☕")) ?></span><?= htmlspecialchars($cat) ?><small><?= $categoryCounts[$cat] ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
                <form class="search-box" method="GET" action="index.php">
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <span>⌕</span><input type="search" name="search" placeholder="Find a dish..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" aria-label="Search">→</button>
                </form>
            </div>

            <?php if (!$menu): ?>
                <div class="empty-menu"><span>♨</span><h3>No dishes found</h3><p>Try another search or category.</p><a class="outline-button" href="index.php">Clear filters</a></div>
            <?php else: ?>
                <div class="food-grid">
                <?php foreach ($menu as $item): ?>
                    <article class="food-card">
                        <div class="food-card-top">
                            <span class="food-category"><?= htmlspecialchars($item["category"]) ?></span>
                            <span class="food-symbol"><?= categoryIcon($item["category"]) ?></span>
                        </div>
                        <div class="food-decoration" aria-hidden="true"><span><?= categoryIcon($item["category"]) ?></span></div>
                        <div class="food-card-info">
                            <h3><?= htmlspecialchars($item["item_name"]) ?></h3>
                            <p><?= $item["category"] === "Tiffins" ? "Comforting, freshly made favourites." : ($item["category"] === "Coffee" ? "A little pick-me-up for your day." : "Big flavour, made for happy cravings.") ?></p>
                            <div class="food-card-bottom">
                                <strong><?= money($item["price"]) ?></strong>
                                <button class="add-button" type="button" data-id="<?= (int)$item["id"] ?>" data-name="<?= htmlspecialchars($item["item_name"], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars($item["price"]) ?>">＋ Add</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="order-panel" id="order-panel">
            <div class="order-panel-heading"><div><p class="eyebrow dark-eyebrow">MADE YOUR CHOICE?</p><h2>Your order <span>♥</span></h2></div><span class="bag-icon">▤</span></div>
            <p class="order-helper">Items you add appear in the order list; the bill updates automatically.</p>
            <div class="live-section-label"><span>1</span><strong>Order added</strong><small>Live item list</small></div>
            <div id="cart-items" class="cart-items"><div class="cart-empty"><span>＋</span><strong>Your cart is waiting</strong><small>Add something delicious from the menu.</small></div></div>
            <div class="cart-controls"><button type="button" id="clear-cart" class="clear-cart">Clear order</button><span><b id="cart-count">0</b> items</span></div>
            <div class="live-section-label bill-live-label"><span>2</span><strong>Live bill</strong><small>Updates with every item</small></div>
            <div class="bill-summary">
                <div><span>Subtotal</span><strong id="subtotal">₹0.00</strong></div>
                <div><span>Items added</span><strong id="bill-item-count">0</strong></div>
                <div><span>GST (5%)</span><strong id="tax">₹0.00</strong></div>
                <div class="grand-total"><span>Grand total</span><strong id="grand-total">₹0.00</strong></div>
            </div>
            <form id="bill-form" action="generate_bill.php" method="POST">
                <input type="hidden" name="cart_json" id="cart-json">
                <button type="submit" class="checkout-button" id="checkout-button" disabled>Generate bill <span>→</span></button>
            </form>
            <div class="secure-note"><span>✓</span> Review your order before billing</div>
        </aside>
    </section>

    <section class="bottom-banner"><div class="banner-mark">J</div><div><strong>Good food. Happy mood.</strong><p>Thanks for making us part of your day.</p></div><a href="bill_history.php">View previous bills ↗</a></section>
    <footer class="site-footer"><span>© <?= date("Y") ?> Jeevz</span><span>MADE WITH CARE <b>♥</b></span></footer>
</main>

<script>
const CART_KEY = "jeevzCart";
let cart = {};
try { cart = JSON.parse(sessionStorage.getItem(CART_KEY) || "{}") || {}; } catch(e) { cart = {}; }
const money = n => "₹" + Number(n).toFixed(2);
const safeText = value => String(value).replace(/[&<>"']/g, char => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[char]));

function saveCart() { sessionStorage.setItem(CART_KEY, JSON.stringify(cart)); renderCart(); }
function renderCart() {
    const container = document.getElementById("cart-items");
    const entries = Object.values(cart);
    let count = 0, subtotal = 0;
    entries.forEach(item => { count += item.qty; subtotal += item.qty * item.price; });
    document.getElementById("cart-count").textContent = count;
    document.getElementById("header-cart-count").textContent = count;
    document.getElementById("subtotal").textContent = money(subtotal);
    document.getElementById("bill-item-count").textContent = count;
    document.getElementById("tax").textContent = money(subtotal * 0.05);
    document.getElementById("grand-total").textContent = money(subtotal * 1.05);
    document.getElementById("checkout-button").disabled = entries.length === 0;
    document.getElementById("cart-json").value = JSON.stringify(cart);

    if (!entries.length) {
        container.innerHTML = '<div class="cart-empty"><span>＋</span><strong>Your cart is waiting</strong><small>Add something delicious from the menu.</small></div>';
        return;
    }
    container.innerHTML = entries.map(item => `
        <div class="cart-item">
            <div class="cart-item-mark">♨</div>
            <div class="cart-item-detail"><strong>${safeText(item.name)}</strong><small>${money(item.price)} each</small>
                <div class="qty-control"><button type="button" data-action="minus" data-id="${item.id}" aria-label="Decrease quantity">−</button><span>${item.qty}</span><button type="button" data-action="plus" data-id="${item.id}" aria-label="Increase quantity">＋</button></div>
            </div>
            <div class="cart-item-end"><strong>${money(item.price * item.qty)}</strong><button type="button" class="remove-item" data-action="remove" data-id="${item.id}">Remove</button></div>
        </div>`).join("");
}
document.querySelectorAll(".add-button").forEach(button => button.addEventListener("click", () => {
    const id = button.dataset.id;
    if (!cart[id]) cart[id] = {id: Number(id), name: button.dataset.name, price: Number(button.dataset.price), qty: 0};
    cart[id].qty++;
    saveCart();
    button.classList.add("added"); button.innerHTML = "✓ Added";
    setTimeout(() => { button.classList.remove("added"); button.innerHTML = "＋ Add"; }, 850);
}));
document.getElementById("cart-items").addEventListener("click", event => {
    const button = event.target.closest("button[data-action]");
    if (!button) return;
    const {action, id} = button.dataset;
    if (!cart[id]) return;
    if (action === "plus") cart[id].qty++;
    if (action === "minus") cart[id].qty--;
    if (action === "remove" || cart[id].qty <= 0) delete cart[id];
    saveCart();
});
document.getElementById("clear-cart").addEventListener("click", () => { cart = {}; saveCart(); });
document.getElementById("bill-form").addEventListener("submit", event => {
    if (!Object.keys(cart).length) { event.preventDefault(); return; }
    document.getElementById("cart-json").value = JSON.stringify(cart);
});
renderCart();
</script>
</body>
</html>