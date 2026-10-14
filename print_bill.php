<?php
require_once "db.php";
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) { header("Location: bill_history.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM bills WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$bill) { header("Location: bill_history.php"); exit; }

$stmt = $conn->prepare("SELECT item_name, unit_price, quantity, line_total FROM bill_items WHERE bill_id = ? ORDER BY id");
$stmt->bind_param("i", $id);
$stmt->execute();
$itemsResult = $stmt->get_result();
$items = [];
while ($row = $itemsResult->fetch_assoc()) $items[] = $row;
$stmt->close();
function money($amount) { return "₹" . number_format((float)$amount, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bill #<?= (int)$bill["id"] ?> | Jeevz</title><link rel="stylesheet" href="style.css">
</head>
<body class="receipt-page">
<div class="receipt-actions no-print"><a class="outline-button" href="index.php">← Back to menu</a><button class="checkout-button print-button" onclick="window.print()">▣ Print bill</button></div>
<main class="receipt">
    <div class="receipt-top-decoration"></div>
    <header class="receipt-header">
        <div class="receipt-logo">♨</div>
        <h1>Jeevz</h1>
        <p class="receipt-tagline">GOOD FOOD <span>•</span> HAPPY MOOD</p>
        <div class="receipt-address"><strong>Jeevz Restaurant</strong><span>Freshly made, served with love</span></div>
    </header>
    <div class="receipt-meta">
        <div><span>Bill number</span><strong>#<?= str_pad((string)$bill["id"], 5, "0", STR_PAD_LEFT) ?></strong></div>
        <div><span>Date & time</span><strong><?= date("d M Y", strtotime($bill["created_at"])) ?></strong><small><?= date("h:i A", strtotime($bill["created_at"])) ?></small></div>
        <div class="thank-you-note">Thank you! <small>for dining with us ♥</small></div>
    </div>
    <div class="receipt-table-wrap"><table class="receipt-table"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>
    <?php foreach ($items as $item): ?><tr><td><?= htmlspecialchars($item["item_name"]) ?></td><td><?= (int)$item["quantity"] ?></td><td><?= number_format((float)$item["unit_price"], 2) ?></td><td><?= number_format((float)$item["line_total"], 2) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <div class="receipt-totals">
        <div><span>Subtotal</span><strong><?= money($bill["subtotal"]) ?></strong></div>
        <div><span>GST (5%)</span><strong><?= money($bill["tax_amount"]) ?></strong></div>
        <div class="receipt-grand"><span>Grand total</span><strong><?= money($bill["grand_total"]) ?></strong></div>
    </div>
    <div class="receipt-footer"><div class="receipt-heart">♥</div><h2>Come back soon!</h2><p>We hope to see you again.</p><div class="receipt-footer-rule">✦ ───────────── ✦</div><small>THIS IS A COMPUTER-GENERATED BILL</small></div>
</main>
<script>if (new URLSearchParams(location.search).get("autoprint") === "1") window.addEventListener("load", () => window.print());</script>
</body>
</html>