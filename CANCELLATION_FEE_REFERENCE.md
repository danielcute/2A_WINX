# CANCELLATION FEE STRUCTURE - QUICK REFERENCE

## Current Policy (As Implemented)

```
┌─────────────────────────────────────────────────────────────┐
│                 CANCELLATION FEE SCHEDULE                   │
└─────────────────────────────────────────────────────────────┘

TIMING                      CANCELLATION FEE        WHAT HAPPENS
────────────────────────────────────────────────────────────────
60+ days before event       ₱2,000                 Forfeit ₱2k
                            (from deposit)          Rest refunded
────────────────────────────────────────────────────────────────
30-59 days before event     ₱5,000                 Forfeit ₱5k
                            (from deposit)          Rest refunded
────────────────────────────────────────────────────────────────
Less than 30 days           100% Forfeiture        No refund
                            (entire deposit)        All lost
────────────────────────────────────────────────────────────────

DEPOSIT REQUIREMENT: 50% of total booking amount
BALANCE DUE: 2 weeks before event
```

## Example Scenarios

### Scenario 1: Large Booking with Early Cancellation
```
Total Booking Amount:     ₱50,000
Deposit (50%):            ₱25,000 [PAID]
Remaining Balance:        ₱25,000 [NOT YET DUE]

Customer cancels 70 days before event:
→ Cancellation Fee: ₱2,000
→ Refund to Customer: ₱23,000
→ Money Retained by Business: ₱2,000
```

### Scenario 2: Medium Booking with Mid-Term Cancellation
```
Total Booking Amount:     ₱30,000
Deposit (50%):            ₱15,000 [PAID]
Remaining Balance:        ₱15,000 [NOT YET DUE]

Customer cancels 45 days before event:
→ Cancellation Fee: ₱5,000
→ Refund to Customer: ₱10,000
→ Money Retained by Business: ₱5,000
```

### Scenario 3: Any Booking with Last-Minute Cancellation
```
Total Booking Amount:     Any Amount
Deposit (50%):            Any Amount [PAID]
Remaining Balance:        Any Amount [NOT YET DUE]

Customer cancels 20 days before event:
→ Cancellation Fee: 100% of deposit
→ Refund to Customer: ₱0
→ Money Retained by Business: 100% of deposit paid
```

## Rescheduling Policy

```
RESCHEDULING TIMEFRAME          FEE
─────────────────────────────────────
60+ days before event           FREE
30-59 days before event         FREE (if slots available)
15-29 days before event         ₱3,000 fee applies
Less than 15 days               Cannot reschedule - cancellation only
```

## Business Logic

1. **When calculating cancellation fee:**
   - Use `event_date` from booking table
   - Calculate days until event
   - Apply appropriate tier
   - Always process refunds within 7-10 business days

2. **When rescheduling:**
   - Check availability of new date
   - Apply ₱3,000 fee if within 30 days
   - Update booking record
   - Send new confirmation email

3. **Special Cases:**
   - Force majeure (weather, disasters): Full refund regardless of date
   - Business cancellation: Full refund to customer
   - Venue unavailable: Reschedule free or full refund

## Storage Location

**Modal Location**: `/public/booking-agreement-modal.php`

**Edit These Lines to Change Fees**:

Line ~100 - 60+ days section:
```html
<p><strong>Cancellation Fee: ₱2,000</strong></p>
```

Line ~115 - 30-59 days section:
```html
<p><strong>Cancellation Fee: ₱5,000</strong></p>
```

Line ~130 - Less than 30 days section:
```html
<p><strong>Cancellation Fee: 100% (Full Forfeiture)</strong></p>
```

## Database Fields Needed

To track cancellations and fees, add to `plans_tbl`:

```sql
ALTER TABLE plans_tbl ADD COLUMN terms_agreed_at DATETIME DEFAULT NULL;
ALTER TABLE plans_tbl ADD COLUMN cancellation_tier VARCHAR(20) DEFAULT NULL;
ALTER TABLE plans_tbl ADD COLUMN cancellation_fee_applied INT DEFAULT 0;
ALTER TABLE plans_tbl ADD COLUMN agreement_version VARCHAR(10) DEFAULT '1.0';
```

## Reporting

**To know total cancellation fees collected:**
```sql
SELECT 
    SUM(cancellation_fee_applied) as total_fees,
    COUNT(*) as cancellations,
    AVG(cancellation_fee_applied) as avg_fee
FROM plans_tbl 
WHERE status = 'cancelled' 
AND cancellation_fee_applied > 0;
```

**To identify which tier customers cancel most:**
```sql
SELECT 
    cancellation_tier,
    COUNT(*) as count,
    SUM(cancellation_fee_applied) as fees_collected
FROM plans_tbl 
WHERE status = 'cancelled'
GROUP BY cancellation_tier;
```

## Communication to Customers

### When Booking Confirmed Email:
```
Dear [Customer Name],

Your booking is confirmed! 

BOOKING DETAILS:
- Event Date: [DATE]
- Booking Amount: [AMOUNT]
- Deposit Paid: 50% (₱[DEPOSIT])
- Balance Due: 2 weeks before event

IMPORTANT - CANCELLATION POLICY:
- Cancellation 60+ days before: ₱2,000 fee
- Cancellation 30-59 days before: ₱5,000 fee
- Cancellation less than 30 days: Full forfeit

Full terms available: [LINK TO TERMS PAGE]

Questions? Reply to this email or contact us.

Best regards,
SINTA Event Planning
```

### When Cancellation Requested:
```
Dear [Customer Name],

Your cancellation has been processed.

CANCELLATION DETAILS:
- Original Booking Amount: ₱[AMOUNT]
- Deposit Paid: ₱[DEPOSIT]
- Days Until Event: [DAYS]
- Applicable Fee: [FEE TIER] - ₱[FEE AMOUNT]
- Refund Amount: ₱[REFUND]

Refund will be processed to your original payment method within 7-10 business days.

Thank you for understanding.

SINTA Event Planning
```

## FAQs for Customers

**Q: Can I cancel my booking?**
A: Yes, you can cancel anytime. A cancellation fee will apply based on when you cancel.

**Q: What if I cancel more than 60 days before?**
A: You'll forfeit ₱2,000 from your deposit. The rest will be refunded.

**Q: What if I cancel within 30 days?**
A: Unfortunately, the entire deposit is forfeited as we cannot book other events that close to your original date.

**Q: Can I reschedule instead?**
A: Yes! You can reschedule free of charge up to 60 days before your event.

**Q: How long does refund take?**
A: Refunds are processed within 7-10 business days to your original payment method.

**Q: What if the venue cancels?**
A: We will offer a full refund or free reschedule to another date.
