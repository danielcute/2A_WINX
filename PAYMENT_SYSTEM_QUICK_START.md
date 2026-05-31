# Payment System Quick Integration Guide

## Step 1: Database Setup (5 minutes)

Run the migration to create payment tables:

```bash
php migrations/AddPaymentReceiptTables.php
```

Or if using a migration runner:
```bash
cd /xampp/htdocs/SINTA
php -r "include 'migrations/AddPaymentReceiptTables.php'; AddPaymentReceiptTables::up(new mysqli('localhost', 'root', '', 'sinta_db'));"
```

**Verify tables created:**
```sql
SHOW TABLES LIKE 'payment%';
-- Should show: payments_tbl, payment_receipts_tbl
```

---

## Step 2: Environment Configuration (5 minutes)

Create or update `.env` file in project root:

```env
# Application
APP_URL=http://localhost/SINTA
APP_ENV=development

# Payment Gateway Credentials (Get from payment providers)
GCASH_MERCHANT_ID=GCASH_TEST_12345
PAYMAYA_PUBLIC_KEY=pk_test_xxxxx
PAYMAYA_SECRET_KEY=sk_test_xxxxx
ATM_MERCHANT_ID=ATM_TEST_12345
```

---

## Step 3: Add Payment Modal to Views (10 minutes)

In your event detail page or checkout page, include the payment modal component:

### Option A: In PHP view file

```php
<?php
// At the top of your page
include 'app/views/components/payment-modal.php';
?>

<!-- In your HTML, where you want the payment button -->
<button class="btn btn-primary" onclick="openPaymentModal(
    <?php echo $planId; ?>, 
    'deposit', 
    <?php echo ($totalPrice * 0.5); ?>
)">
    Pay Deposit - ₱<?php echo number_format($totalPrice * 0.5, 2); ?>
</button>
```

### Option B: In existing event-detail.php

Find the payment status section and add buttons:

```php
<div class="payment-actions">
    <?php
    $paymentSummary = $paymentService->getPaymentSummary($planId);
    $balance = $paymentSummary['balance_remaining'];
    
    if ($balance > 0):
        if ($paymentSummary['total_paid'] == 0):
            // No payment yet - show deposit button
    ?>
            <button class="btn btn-primary" onclick="openPaymentModal(<?php echo $planId; ?>, 'deposit', <?php echo ($totalPrice * 0.5); ?>)">
                <i class="fas fa-credit-card"></i> Pay Deposit (₱<?php echo number_format($totalPrice * 0.5, 2); ?>)
            </button>
    <?php 
        else:
            // Deposit paid - show final payment button
    ?>
            <button class="btn btn-primary" onclick="openPaymentModal(<?php echo $planId; ?>, 'final', <?php echo $balance; ?>)">
                <i class="fas fa-credit-card"></i> Pay Balance (₱<?php echo number_format($balance, 2); ?>)
            </button>
    <?php
        endif;
    endif;
    ?>
</div>
```

---

## Step 4: Display Payment Progress (5 minutes)

Add this to show current payment status:

```php
<?php
require_once 'app/services/PaymentService.php';
$paymentService = new Services\PaymentService();
$summary = $paymentService->getPaymentSummary($planId);
?>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">💳 Payment Status</h5>
    </div>
    <div class="card-body">
        <!-- Progress Bar -->
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-2">
                <span>Payment Progress</span>
                <span><?php echo round(($summary['total_paid'] / $totalPrice) * 100); ?>%</span>
            </div>
            <div class="progress">
                <div class="progress-bar progress-bar-striped" 
                     style="width: <?php echo ($summary['total_paid'] / $totalPrice) * 100; ?>%">
                </div>
            </div>
        </div>

        <!-- Amount Summary -->
        <div class="row text-center">
            <div class="col-md-4">
                <div class="text-muted">Paid</div>
                <div class="h5" style="color: #28a745;">₱<?php echo number_format($summary['total_paid'], 2); ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted">Balance</div>
                <div class="h5" style="color: #dc3545;">₱<?php echo number_format($summary['balance_remaining'], 2); ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted">Total</div>
                <div class="h5">₱<?php echo number_format($totalPrice, 2); ?></div>
            </div>
        </div>
    </div>
</div>
```

---

## Step 5: Display Receipt History (5 minutes)

Show all payment receipts for an event:

```php
<?php
$receipts = $paymentService->getReceiptsByPlanId($planId);
?>

<div class="card mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">📄 Payment Receipts</h5>
    </div>
    <div class="card-body">
        <?php if (count($receipts) > 0): ?>
            <div class="list-group">
                <?php foreach ($receipts as $receipt): ?>
                    <a href="#" class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">
                                <?php echo ucfirst($receipt['receipt_type']); ?> Payment
                                <span class="badge badge-success">Confirmed</span>
                            </h6>
                            <small><?php echo date('M d, Y', strtotime($receipt['created_at'])); ?></small>
                        </div>
                        <p class="mb-1">
                            Amount: <strong>₱<?php echo $receipt['amount_paid']; ?></strong>
                            • <?php echo strtoupper(str_replace('_', ' ', $receipt['payment_method'])); ?>
                        </p>
                        <small class="text-muted">
                            Receipt #<?php echo $receipt['receipt_number']; ?> 
                            <?php if ($receipt['reference_number']): ?>
                                | Ref: <?php echo $receipt['reference_number']; ?>
                            <?php endif; ?>
                        </small>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="downloadReceipt('<?php echo $receipt['receipt_number']; ?>')">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted text-center">No payments recorded yet</p>
        <?php endif; ?>
    </div>
</div>
```

---

## Step 6: Handle Payment Callbacks (Backend)

Already handled in `api-payment.php`, but for webhook verification:

### For GCash Webhook
Add this to your webhook handler or payment gateway:

```php
// In your webhook handler (e.g., app/webhooks/gcash.php)
$payload = json_decode(file_get_contents('php://input'), true);

if ($payload['status'] === 'success') {
    file_get_contents('http://localhost/SINTA/public/api-payment.php', false, 
        stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'action' => 'gcash_callback',
                    'payment_id' => $payload['payment_id'],
                    'status' => 'completed',
                    'transaction_id' => $payload['transaction_id']
                ])
            ]
        ])
    );
}
```

---

## Step 7: Test the Payment System (10 minutes)

### Test Flow:
1. **Navigate to an event detail page**
2. **Click "Pay Deposit" button**
3. **Select payment method (GCash for testing)**
4. **Enter test credentials:**
   - Mobile: 09999999999
   - Amount: Auto-calculated (50% of total)
5. **See confirmation modal**
6. **Verify payment in database:**
   ```sql
   SELECT * FROM payments_tbl WHERE plan_id = YOUR_PLAN_ID;
   SELECT * FROM payment_receipts_tbl WHERE plan_id = YOUR_PLAN_ID;
   ```

---

## Step 8: Customize Receipt Template (Optional)

Edit the receipt HTML in `app/services/PaymentService.php` → `renderReceiptTemplate()` method to match your branding.

---

## API Endpoint Reference

### Pay Now
```javascript
// JavaScript example
openPaymentModal(planId, 'deposit', amount);
```

### Get Payment Summary
```bash
curl "http://localhost/SINTA/public/api-payment.php?action=get_payment_summary&plan_id=1"
```

### Get All Receipts
```bash
curl "http://localhost/SINTA/public/api-payment.php?action=get_receipts&plan_id=1"
```

### Download Receipt
```bash
curl "http://localhost/SINTA/public/api-payment.php?action=download_receipt&receipt_id=1" 
     > receipt.html
```

---

## Checklist

- [ ] Database migration completed
- [ ] .env file configured with payment keys
- [ ] Payment modal included in views
- [ ] Payment button added to event detail page
- [ ] Payment progress display added
- [ ] Receipt history display added
- [ ] Tested deposit payment flow
- [ ] Tested final payment flow
- [ ] Tested receipt download
- [ ] Verified emails sent
- [ ] Admin can verify manual payments

---

## Troubleshooting Common Issues

### Issue: "Payment method not supported"
**Solution:** Verify payment gateway class name matches in `PaymentGateways.php`

### Issue: Receipt not generating
**Solution:** Check that user/plan data exists and `/logs/` directory is writable

### Issue: Payment modal not showing
**Solution:** Ensure jQuery and Bootstrap are loaded before payment-modal.php

### Issue: Redirect to payment gateway fails
**Solution:** Verify APP_URL in .env is correct and accessible

### Issue: Gateway credentials not working
**Solution:** 
- Verify credentials in .env
- Check APP_ENV is 'development' for test mode
- Contact payment provider for test credentials

---

## Support Contacts

- **GCash Support**: contact@gcash.com
- **PayMaya Support**: support@paymaya.com
- **SINTA Support**: support@sintaevents.com

---

## Next Steps

1. Deploy to production by changing APP_ENV to 'production'
2. Get live API credentials from payment providers
3. Add email notifications for payment confirmations
4. Set up automated reminders for balance payments
5. Add payment reconciliation report to admin dashboard
6. Integrate with accounting system for reporting
