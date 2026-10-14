<?php
require_once "db.php";
$result = $conn->query("SELECT id, subtotal, tax_amount, grand_total, created_at FROM bills ORDER BY id DESC LIMIT 100");
$bills = [];
while ($row = $result->fetch_assoc()) $bills[] = $row;
function money($amount) { return "₹" . number_format((float)$amount, 2); }
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Bill History | Jeevz</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="site-header">
    <a class="brand" href="index.php"><span class="brand-icon">♨</span><span><strong>Jeevz</strong><small>GOOD FOOD • HAPPY MOOD</small></span></a>
    <nav class="main-nav"><a href="index.php">Menu & Billing</a><a href="manage_menu.php">Manage Menu</a><a class="nav-active" href="bill_history.php">Bill History</a></nav>
    <a class="header-pill" href="index.php">← Back to menu</a>
</header>
<main class="page-wrap history-page">
    <section class="history-hero"><p class="eyebrow">YOUR RECORDS</p><h1>Every order, <em>in one place.</em></h1><p>Review previous bills and reprint a receipt whenever you need it.</p></section>
    <section class="history-panel">
        <div class="history-heading"><div><h2>Bill history</h2><p><?= count($bills) ?> recent bill(s)</p></div><a class="outline-button" href="index.php">＋ New order</a></div>
        <?php if (!$bills): ?>
            <div class="empty-menu"><span>▤</span><h3>No bills yet</h3><p>Your completed orders will appear here.</p><a class="checkout-button inline-button" href="index.php">Start an order →</a></div>
        <?php else: ?>
        <div class="table-scroll"><table class="history-table"><thead><tr><th>BILL</th><th>DATE & TIME</th><th>SUBTOTAL</th><th>GST</th><th>GRAND TOTAL</th><th>ACTION</th></tr></thead><tbody>
        <?php foreach ($bills as $bill): ?><tr>
            <td><span class="bill-number">#<?= str_pad((string)$bill["id"], 5, "0", STR_PAD_LEFT) ?></span></td>
            <td><strong><?= date("d M Y", strtotime($bill["created_at"])) ?></strong><small><?= date("h:i A", strtotime($bill["created_at"])) ?></small></td>
            <td><?= money($bill["subtotal"]) ?></td><td><?= money($bill["tax_amount"]) ?></td><td><strong class="history-total"><?= money($bill["grand_total"]) ?></strong></td>
            <td><a class="small-action" href="print_bill.php?id=<?= (int)$bill["id"] ?>">View / Print ↗</a></td>
        </tr><?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </section>
    <footer class="site-footer"><span>© <?= date("Y") ?> Jeevz</span><span>MADE WITH CARE <b>♥</b></span></footer>
</main>
</body></html>