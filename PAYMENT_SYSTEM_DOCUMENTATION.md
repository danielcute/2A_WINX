# Payment System Implementation Guide

## Overview

This comprehensive payment system enables multiple payment methods for partial (deposit) and full payments with automatic receipt generation.

---

## Features

### ✅ Payment Methods Supported
- **GCash** - Mobile wallet payment
- **PayMaya** - Online payment platform
- **Bank Transfer** - Direct bank deposit
- **ATM Card** - Debit/Credit card payment

### ✅ Payment Types
- **Deposit (50%)** - Initial booking confirmation payment
- **Final Payment (50%)** - Remaining balance due 2 weeks before event
- **Full Payment (100%)** - One-time complete payment option

### ✅ Automatic Receipt Generation
- Partial payment receipts
- Full payment receipts
- Receipt download as HTML/PDF
- Email receipt delivery
- Receipt tracking and history

### ✅ Payment Tracking
- Real-time payment status updates
- Multiple payment history per event
- Balance calculation and tracking
- Payment confirmation notifications

---

## Database Schema

### New Tables Created

#### `payments_tbl` - Payment Transactions
```
payment_id (PK, AUTO_INCREMENT)
plan_id (FK) - Links to plans_tbl
user_id (FK) - Links to users_tbl
payment_type ENUM('deposit', 'full', 'final')
amount DECIMAL(10,2)
payment_method ENUM('gcash', 'paymaya', 'bank_transfer', 'atm_card')
payment_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled')
transaction_id VARCHAR(255) - Gateway transaction ID
reference_number VARCHAR(255) - Reference for manual verification
payment_details JSON - Encrypted payment information
gateway_response JSON - Full gateway response
paid_at TIMESTAMP - Payment completion time
created_at, updated_at TIMESTAMP
```

#### `payment_receipts_tbl` - Receipt Records
```
receipt_id (PK, AUTO_INCREMENT)
plan_id, user_id, payment_id (FKs)
receipt_number VARCHAR(50) - Unique receipt ID (RCP-YYYYMMDD-XXXXX)
receipt_type ENUM('partial', 'full')
subtotal, service_fee, total_amount DECIMAL(10,2)
amount_paid, balance_remaining DECIMAL(10,2)
payment_method, reference_number VARCHAR(255)
items_purchased JSON - Items/packages purchased
recipient_email, recipient_phone VARCHAR
created_at TIMESTAMP
```

#### Enhanced `plans_tbl` Fields
```
total_paid DECIMAL(10,2) - Sum of all completed payments
balance_remaining DECIMAL(10,2) - Remaining amount
payment_count INT - Number of payments received
```

---

## Setup Instructions

### 1. Run Database Migration

```bash
php migrations/migrate.php AddPaymentReceiptTables
```

Or manually execute the migration SQL:
```sql
-- See migrations/AddPaymentReceiptTables.php for full schema
```

### 2. Configure Environment Variables

Create/update `.env` file:
```env
# Payment Gateway Keys (obtain from payment providers)
GCASH_MERCHANT_ID=your_gcash_merchant_id
PAYMAYA_PUBLIC_KEY=your_paymaya_public_key
PAYMAYA_SECRET_KEY=your_paymaya_secret_key
ATM_MERCHANT_ID=your_atm_merchant_id

# Application Settings
APP_URL=http://localhost/SINTA
APP_ENV=development # Use 'production' for live payments
```

### 3. Integration into Event Checkout

#### In `checkout.php` or payment button:

```php
<?php
// Include payment modal
include 'app/views/components/payment-modal.php';
?>

<!-- Payment button on event detail page -->
<button class="btn btn-primary" onclick="openPaymentModal(<?php echo $planId; ?>, 'deposit', <?php echo $depositAmount; ?>)">
    Pay Deposit (₱<?php echo number_format($depositAmount, 2); ?>)
</button>
```

#### In event detail page:

```php
<?php
require_once 'app/services/PaymentService.php';

$paymentService = new Services\PaymentService();
$paymentSummary = $paymentService->getPaymentSummary($planId);
$receipts = $paymentService->getReceiptsByPlanId($planId);
?>

<!-- Display payment progress -->
<div class="payment-progress">
    <h5>Payment Status</h5>
    <div class="progress">
        <div class="progress-bar" style="width: <?php echo ($paymentSummary['total_paid'] / $totalPrice) * 100; ?>%">
            ₱<?php echo number_format($paymentSummary['total_paid'], 2); ?>
        </div>
    </div>
    <p>
        Paid: ₱<?php echo number_format($paymentSummary['total_paid'], 2); ?> / 
        Balance: ₱<?php echo number_format($paymentSummary['balance_remaining'], 2); ?>
    </p>
</div>

<!-- Payment history and receipts -->
<div class="payment-history">
    <h5>Payment History</h5>
    <?php foreach ($receipts as $receipt): ?>
        <div class="receipt-item">
            <p>
                <strong><?php echo ucfirst($receipt['receipt_type']); ?> Payment</strong> - 
                ₱<?php echo $receipt['amount_paid']; ?> on 
                <?php echo date('M d, Y', strtotime($receipt['created_at'])); ?>
            </p>
            <small><?php echo $receipt['payment_method']; ?></small>
            <a href="#" onclick="downloadReceipt(<?php echo $receipt['receipt_id']; ?>)">Download Receipt</a>
        </div>
    <?php endforeach; ?>
</div>
```

---

## API Endpoints

### Process Payment
```http
POST /public/api-payment.php
Content-Type: application/x-www-form-urlencoded

action=process_payment
&plan_id=123
&payment_method=gcash
&payment_type=deposit
&mobile_number=09XXXXXXXXX
```

**Response:**
```json
{
    "success": true,
    "payment_id": 456,
    "transaction_id": "GCASH-1234567890",
    "checkout_url": "https://gcash.com/payment?ref=...",
    "reference_number": "GCASH-123-ABCDE"
}
```

### Complete Payment (Webhook)
```http
POST /public/api-payment.php

action=complete_payment
&payment_id=456
&transaction_id=GCASH-1234567890
&reference_number=GCASH-123-ABCDE
```

### Get Receipts
```http
GET /public/api-payment.php?action=get_receipts&plan_id=123
```

### Get Payment Summary
```http
GET /public/api-payment.php?action=get_payment_summary&plan_id=123
```

**Response:**
```json
{
    "success": true,
    "summary": {
        "total_paid": 3675.00,
        "balance_remaining": 3675.00,
        "completed_payments": 1,
        "pending_payments": 0
    }
}
```

### Download Receipt
```http
GET /public/api-payment.php?action=download_receipt&receipt_id=789
```

---

## Implementation Workflow

### For Deposit Payment

1. **User clicks "Pay Deposit"**
   ```
   openPaymentModal(planId, 'deposit', depositAmount)
   ```

2. **User selects payment method and enters details**
   - GCash: Mobile number
   - PayMaya: Mobile number + name
   - Bank Transfer: Bank details
   - ATM Card: Card information

3. **Frontend sends POST to `/api-payment.php?action=process_payment`**
   - Creates payment record
   - Calls payment gateway API
   - Returns checkout URL or reference

4. **For online payments**: Redirect to payment gateway
   **For bank transfer**: Display bank details with reference number

5. **Payment gateway (or admin) confirms payment**
   - Webhook triggers `/api-payment.php?action=complete_payment`
   - Or admin manually confirms in admin panel

6. **Automatic Receipt Generation**
   - Receipt created and stored in database
   - Email sent to user
   - Status updated: `payment_status='paid'`

7. **User can download receipt**
   - HTML format for immediate viewing
   - Can be converted to PDF by user's browser

### For Final Payment

Same flow as deposit, but:
- `payment_type='final'`
- Amount is remaining 50% balance
- Receipt marked as "FULL PAYMENT" if completed

---

## Payment Method Specifics

### GCash
- **Endpoint**: https://app.gcash.com
- **Initiation**: SMS sent to registered number
- **Webhook**: Automatic callback on payment completion
- **Testing**: Use GCASH_MERCHANT_ID in sandbox mode

### PayMaya
- **Endpoint**: https://staging-api.paymaya.com (dev) / https://api.paymaya.com (prod)
- **Initiation**: User redirected to PayMaya checkout page
- **Webhook**: HTTP callback to webhook URL
- **Authentication**: Basic auth with public/secret keys

### Bank Transfer
- **Process**: Manual transfer by user
- **Reference**: Unique reference number provided
- **Verification**: Admin verifies and updates status
- **Proof**: User uploads receipt or we match deposit

### ATM Card
- **Process**: Direct card charge (similar to PayMaya)
- **Security**: PCI DSS compliant processing
- **OTP**: One-time password for verification
- **Webhook**: Automatic callback on success

---

## Webhook Handling

### GCash Callback Example
```php
POST /public/api-payment.php
Content-Type: application/json

{
    "action": "gcash_callback",
    "payment_id": 456,
    "status": "success",
    "transaction_id": "GCASH-1234567890"
}
```

### PayMaya Callback Example
```php
POST /public/api-payment.php
Content-Type: application/json

{
    "action": "paymaya_callback",
    "checkout_id": "PM-123456",
    "status": "COMPLETED"
}
```

---

## Receipt Features

### Auto-Generated Receipt Includes
- Receipt number (RCP-YYYYMMDD-XXXXX)
- Customer name, email, phone
- Event details (name, date)
- Payment method and amount
- Balance remaining (if partial)
- Payment date and time
- Professional SINTA branding

### Receipt Status
- **Partial Payment Receipt** - Shows balance due
  - Balance payment reminder
  - Due date (2 weeks before event)
- **Full Payment Receipt** - Marks booking as complete
  - All payment details
  - Event confirmation

### Access Receipt
- View in modal immediately after payment
- Download as HTML file
- Access from user dashboard anytime
- Email copy sent automatically

---

## Admin Panel Integration

### View Payments
```
Admin Dashboard → Bookings → Select Event → Payment History
```

Shows:
- All payment records
- Transaction IDs and references
- Payment method and date
- Amount and status
- Link to receipts

### Verify Manual Payments
For bank transfers:
1. Admin receives transfer notification
2. Verifies amount and reference
3. Clicks "Verify Payment"
4. System creates receipt and updates status

---

## Testing

### Using Test Gateway Credentials

**GCash Test Mode:**
```env
GCASH_MERCHANT_ID=GCASH_TEST_12345
APP_ENV=development
```

Use test phone number: `09999999999`
Test amount: Any amount (up to ₱50,000)

**PayMaya Test Mode:**
```env
PAYMAYA_PUBLIC_KEY=pk_test_your_key
PAYMAYA_SECRET_KEY=sk_test_your_key
APP_ENV=development
```

Test card: `5555555555554444`
Expiry: Any future date
CVV: Any 3 digits

### Bank Transfer Testing
1. Fill in mock bank details
2. Reference number auto-generated
3. Manually mark as verified in admin
4. Receipt generated for testing

---

## Security Considerations

### Payment Data Protection
- Payment details stored in encrypted JSON
- Sensitive fields (card numbers, OTP) not stored
- Only transaction IDs and references stored
- PCI DSS compliant processing

### Webhook Verification
- Webhook signature verification
- IP whitelist for callbacks
- Transaction ID validation
- Amount verification

### User Authentication
- Session verification before payments
- User authorization check (plan owner)
- CSRF token validation
- Rate limiting on payment attempts

---

## Troubleshooting

### Payment not completing
1. Check payment_status in database
2. Verify gateway credentials in .env
3. Check logs in `/logs/payments.log`
4. Verify webhook URL is accessible

### Receipt not generating
1. Check `payment_receipts_tbl` for errors
2. Verify user/plan data exists
3. Check PHP error logs
4. Ensure `logs/` directory writable

### Gateway not responding
1. Verify API credentials
2. Check internet connectivity
3. Verify webhook callback URL
4. Check gateway status page

---

## File Structure

```
app/
  models/
    Payment.php           # Payment model
    Receipt.php          # Receipt model
  services/
    PaymentService.php   # Payment orchestration
    PaymentGateways.php  # Gateway implementations
  views/
    components/
      payment-modal.php  # Payment UI modal
public/
  api-payment.php       # Payment API endpoints
migrations/
  AddPaymentReceiptTables.php  # Database schema
```

---

## Support & Maintenance

### Logging
All payment transactions logged to `/logs/payments.log`:
```json
{
    "timestamp": "2024-01-15 14:30:00",
    "plan_id": 123,
    "transaction_id": "GCASH-1234567890",
    "status": "initiated",
    "response": {...}
}
```

### Backup & Restore
- Regular backups of payment tables
- Transaction records immutable (logged only)
- Receipt archives for compliance

### Updates & Maintenance
- Payment gateway API updates monitored
- Security patches applied immediately
- Rate limits reviewed quarterly
- Test transactions run weekly
