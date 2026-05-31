<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance()->getConnection();

echo "Updating receipt amounts to reflect correct cumulative payments...\n\n";

// Receipt 11 (full payment) should show cumulative amount of 262500, not just 131250
$totalPrice = 262500;
$subtotal = 250000;
$serviceFee = 7500;

// Update receipt 11 to show correct full payment amounts
$stmt = $db->prepare("UPDATE payment_receipts_tbl SET amount_paid = ?, balance_remaining = 0, subtotal = ?, service_fee = ? WHERE receipt_id = 11");
if ($stmt) {
    $stmt->bind_param("ddd", $totalPrice, $subtotal, $serviceFee);
    if ($stmt->execute()) {
        echo "✓ Updated Receipt 11 (Full Payment):\n";
        echo "  - Amount Paid: ₱262,500.00 (was ₱131,250.00)\n";
        echo "  - Balance Remaining: ₱0.00 (was ₱131,250.00)\n";
        echo "  - Total Amount: ₱262,500.00\n";
    }
}

// Verify the update
echo "\n=== UPDATED RECEIPTS ===\n";
$stmt = $db->prepare("SELECT receipt_id, receipt_type, amount_paid, balance_remaining, total_amount FROM payment_receipts_tbl WHERE plan_id = 45 ORDER BY receipt_id");
$stmt->execute();
$receipts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($receipts as $r) {
    echo "Receipt {$r['receipt_id']} ({$r['receipt_type']}): Paid ₱{$r['amount_paid']}, Remaining ₱{$r['balance_remaining']}, Total ₱{$r['total_amount']}\n";
}

echo "\n✓ Receipt amounts updated!\n";
?>
