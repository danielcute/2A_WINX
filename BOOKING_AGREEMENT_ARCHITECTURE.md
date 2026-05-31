# BOOKING AGREEMENT - VISUAL ARCHITECTURE

## User Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        BOOKING FLOW                                      │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│  Checkout Page   │
│  (Form Filled)   │
└────────┬─────────┘
         │
         │ User clicks "Confirm Booking"
         ▼
┌──────────────────────────────────────────────┐
│   BOOKING AGREEMENT MODAL OPENS              │
│  (New Feature - Interactive)                 │
├──────────────────────────────────────────────┤
│  [X]  Modal Header & Close Button            │
│  ┌────────────────────────────────────────┐  │
│  │ 📄 Booking Agreement                   │  │
│  │                                        │  │
│  │ Terms & Conditions                     │  │
│  │ Booking Confirmation                  │  │
│  │ ⚠️  CANCELLATION POLICY (RED HIGHLIGHT)│  │
│  │   60+ days: ₱2,000                    │  │
│  │   30-59 days: ₱5,000                  │  │
│  │   <30 days: 100% forfeit              │  │
│  │ Rescheduling Policy                   │  │
│  │ Payment Terms                         │  │
│  │ Liability & Responsibility            │  │
│  └────────────────────────────────────────┘  │
│                                              │
│  ☐ I agree to terms (DISABLED button)       │
│  [Cancel]  [Disabled: I Agree & Confirm]   │
└────────────┬─────────────────────────────────┘
             │
             │ User checks checkbox
             ▼
┌──────────────────────────────────────────────┐
│  BUTTON BECOMES ENABLED                      │
│                                              │
│  ☑ I agree to terms (ENABLED button)         │
│  [Cancel]  [✓ I Agree & Confirm] ← BLUE    │
└────────────┬─────────────────────────────────┘
             │
             │ User clicks "I Agree & Confirm"
             ▼
┌──────────────────────────────────────────────┐
│  FORM VALIDATION OCCURS                      │
│  Checking:                                   │
│  ✓ Event date selected                      │
│  ✓ Event time selected                      │
│  ✓ Full name filled                         │
│  ✓ Email valid                              │
│  ✓ Phone number filled                      │
└────────────┬─────────────────────────────────┘
             │
             ├─ If validation fails ─────────────┐
             │                                   │
             │                          ▼
             │                  ┌──────────────┐
             │                  │ Toast Error  │
             │                  │ Message      │
             │                  └──────────────┘
             │                  (Modal stays open)
             │
             └─ If validation passes ───────────┐
                                               │
                                        ▼
                                ┌──────────────┐
                                │ Modal Closes │
                                └──────┬───────┘
                                       │
                                       ▼
                        ┌──────────────────────┐
                        │ Toast Success Message │
                        │ "Processing booking" │
                        └──────────────────────┘
                                       │
                                       ▼
                    ┌────────────────────────────┐
                    │  PROCEED TO PAYMENT        │
                    │  (Backend Integration)     │
                    │  - Process 50% deposit     │
                    │  - Store booking in DB     │
                    │  - Send confirmation email │
                    └────────────────────────────┘
```

---

## Modal Structure

```
┌─────────────────────────────────────────────────────────────┐
│ BOOKING AGREEMENT MODAL (600px max width, 85vh max height)  │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  📄 Booking Agreement                                   [X]  │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│                     SCROLLABLE CONTENT (↕)                   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ TERMS & CONDITIONS                                     │ │
│  │ Introduction paragraph                                │ │
│  │                                                        │ │
│  │ ✓ BOOKING CONFIRMATION                                │ │
│  │   ✓ Non-refundable deposit (50%)                      │ │
│  │   ✓ Booking confirmation details                      │ │
│  │   ✓ Balance due 2 weeks before                        │ │
│  │   ✓ Event date cannot change                          │ │
│  │                                                        │ │
│  │ ⚠️  CANCELLATION POLICY (Red Background Box)           │ │
│  │   ┌──────────────────────────────────────────────┐    │ │
│  │   │ 60+ days before:  ₱2,000 fee                │    │ │
│  │   │ 30-59 days before: ₱5,000 fee              │    │ │
│  │   │ <30 days: 100% forfeiture                   │    │ │
│  │   │ Note: Refunds within 7-10 business days     │    │ │
│  │   └──────────────────────────────────────────────┘    │ │
│  │                                                        │ │
│  │ ✓ RESCHEDULING                                        │ │
│  │   ✓ Free up to 60 days before                        │ │
│  │   ✓ ₱3,000 fee within 30 days                        │ │
│  │   ✓ Based on availability                            │ │
│  │                                                        │ │
│  │ 💳 PAYMENT TERMS                                       │ │
│  │   ✓ Multiple payment methods                         │ │
│  │   ✓ All prices include 12% VAT                       │ │
│  │   ✓ Late payment = postponement risk                 │ │
│  │                                                        │ │
│  │ 🛡️  LIABILITY & RESPONSIBILITY                          │ │
│  │   ✓ Force majeure clause                             │ │
│  │   ✓ Venue/vendor disclaimers                         │ │
│  │   ✓ Client responsibility for info                   │ │
│  │                                                        │ │
│  │ FINAL CONFIRMATION                                   │ │
│  │   I acknowledge that:                               │ │
│  │   ✓ I have read all terms                           │ │
│  │   ✓ I understand cancellation policy                │ │
│  │   ✓ I authorize 50% deposit charge                  │ │
│  │   ✓ I provide accurate information                  │ │
│  │   ✓ I accept receiving updates via email/phone      │ │
│  │                                                        │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│  FOOTER (Fixed - always visible)                             │
│                                                              │
│  ☐ I agree and understand all terms                         │
│    (Checkbox - user must check)                             │
│                                                              │
│  [Cancel]                  [I Agree & Confirm Booking]      │
│  (Gray button)             (Blue button - starts disabled)   │
│                                                              │
│  Agreement effective from: May 29, 2026                      │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Cancellation Fee Timeline

```
EVENT DATE TIMELINE WITH CANCELLATION FEES

┌────────────────────────────────────────────────────────────┐
│                                                             │
│  Today                                        Event Date    │
│    ▲                                              ▲         │
│    │                                              │         │
│    │◄─────────────────60+ days─────────────────►│         │
│    │                                              │         │
│    ├─ Zone 1: 60+ days before                    │         │
│    │  CANCELLATION FEE: ₱2,000                  │         │
│    │  STATUS: Refund OK                          │         │
│    │                                              │         │
│    │◄────────────30-59 days──────────────────►│         │
│    │                      │                      │         │
│    ├───────┬──────────────┤                      │         │
│    │       │ Zone 2       │                      │         │
│    │       │ CANCELLATION FEE: ₱5,000            │         │
│    │       │ STATUS: Refund reduced              │         │
│    │       │                                      │         │
│    │       ├─ Zone 3: <30 days ───────────────►│         │
│    │       │              │                      │         │
│    │       │              ├────────────────────→│         │
│    │       │              │ CANCELLATION FEE: 100%         │
│    │       │              │ STATUS: NO REFUND             │
│    │       │              │                      │         │
│    │       │              │                      │         │
└────┼───────┼──────────────┼──────────────────────┴────────┘
     │       │              │
     ▼       ▼              ▼
  2 MONTHS 1 MONTH   30 DAYS (DANGER ZONE)


CUSTOMER DECISION AT EACH POINT:

If booking ₱50,000 with ₱25,000 deposit paid:

┌────────────────────────────────────────────┐
│ Cancel 90 days before:                     │
│ Deposit: ₱25,000 ─► Fee: ₱2,000           │
│ Refund: ₱23,000                           │
│ Business keeps: ₱2,000 (8%)                │
└────────────────────────────────────────────┘

┌────────────────────────────────────────────┐
│ Cancel 45 days before:                     │
│ Deposit: ₱25,000 ─► Fee: ₱5,000           │
│ Refund: ₱20,000                           │
│ Business keeps: ₱5,000 (20%)               │
└────────────────────────────────────────────┘

┌────────────────────────────────────────────┐
│ Cancel 20 days before:                     │
│ Deposit: ₱25,000 ─► Fee: 100%             │
│ Refund: ₱0                                 │
│ Business keeps: ₱25,000 (100%)             │
└────────────────────────────────────────────┘
```

---

## Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    USER INTERACTION                         │
└─────────────────────────────────────────────────────────────┘
                            │
                    ┌───────▼────────┐
                    │  Frontend JS   │
                    │ (Modal Logic)  │
                    └───────┬────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
    Form Data      Agreement       User        Toast
    Validation     Checkbox        Input       Notify
        │              │             │             │
        └───────────────┼─────────────┴─────────────┘
                        │
            ┌───────────▼───────────┐
            │  Form Submission      │
            │  (bookingForm data)   │
            └───────────┬───────────┘
                        │
        ┌───────────────▼───────────────────┐
        │                                   │
        ▼                                   ▼
   Backend (Future)              Database (Future)
   - Validate                     - Store booking
   - Create booking               - Track agreement
   - Process payment              - Log timestamp
   - Send email

                        ↓

            ┌───────────────────────┐
            │  Confirmation Email   │
            │  (With agreement copy)│
            └───────────────────────┘
```

---

## State Machine

```
┌──────────────┐
│  CLOSED      │
│ Modal Hidden │
└──────┬───────┘
       │
       │ confirmBooking() called
       │
       ▼
┌─────────────────────────┐
│  OPEN / REVIEWING       │
│  Modal Visible          │
│  Checkbox: Unchecked    │
│  Button: Disabled       │
└──────┬──────────────────┘
       │
       │ Checkbox checked
       │
       ▼
┌─────────────────────────┐
│  AGREED                 │
│  Modal Visible          │
│  Checkbox: Checked      │
│  Button: Enabled        │
└──────┬──────────────────┘
       │
       │ User clicks "I Agree"
       ├─────────────────────┐
       │                     │
       │ Validation          │
       │ Success             │
       │                     │
       ▼                     ▼
┌─────────────────┐   ┌──────────────┐
│  PROCESSING     │   │  VALIDATION  │
│  Showing toast  │   │  FAILED      │
│  Modal closing  │   │  Error shown │
│  Preparing data │   │  Modal open  │
└──────┬──────────┘   │  (stay open) │
       │              └──────────────┘
       │
       ▼
┌──────────────┐
│  SUBMITTED   │
│  Modal Hidden│
│  Forwarding  │
│  to Payment  │
└──────────────┘
```

---

## Responsive Design Breakpoints

```
┌─────────────────────────────────────────────────────────────┐
│                  RESPONSIVE DESIGN LAYOUT                   │
└─────────────────────────────────────────────────────────────┘

DESKTOP (1200px+)           TABLET (768px-1199px)
┌─────────────────────┐     ┌─────────────────┐
│                     │     │                 │
│   Modal (600px)     │     │  Modal (95%)    │
│                     │     │                 │
│ Side-by-side        │     │ Side-by-side    │
│ buttons at bottom   │     │ buttons         │
│                     │     │                 │
│ Full padding        │     │ Adjusted pad    │
│                     │     │                 │
└─────────────────────┘     └─────────────────┘


MOBILE (480px-767px)        SMALL MOBILE (< 480px)
┌──────────────┐            ┌────────────┐
│              │            │            │
│ Modal (90%)  │            │Modal (98%) │
│              │            │            │
│ Stacked      │            │ Stacked    │
│ buttons      │            │ buttons    │
│ (vertical)   │            │            │
│              │            │ Reduced    │
│ Reduced      │            │ padding    │
│ padding      │            │            │
│              │            │ Smaller    │
│              │            │ font       │
│              │            │            │
└──────────────┘            └────────────┘
```

---

## Color Coding Legend

```
✅ GREEN COLORS
   - Primary actions (✓ buttons)
   - Available status
   - Success messages

⚠️  ORANGE/YELLOW COLORS
   - Info boxes
   - Important notes
   - Warnings

❌ RED COLORS
   - Cancellation policy (emphasis)
   - Error messages
   - Critical information

⚪ NEUTRAL COLORS
   - Text (gray)
   - Borders
   - Secondary elements
   - Disabled state
```

---

## Integration Points

```
┌─────────────────────────────────────────────────────────────┐
│                   INTEGRATION DIAGRAM                       │
└─────────────────────────────────────────────────────────────┘

checkout.php ◄──────────────────────────────────────────┐
  │                                                      │
  ├─ Form Data Collection                              │
  │  (event date, contact info)                        │
  │                                                     │
  ├─ Include Modal File ──────────────────────────────►│
  │                                                     │
  └─ Submit Button ──────────────────────────────────┐ │
                                                      │ │
                                                      ▼ ▼
                              booking-agreement-modal.php
                                           │
                                ┌──────────┼──────────┐
                                │          │          │
                                ▼          ▼          ▼
                            HTML + CSS + JavaScript
                                │
                    ┌───────────┴───────────┐
                    │                       │
                    ▼                       ▼
              Frontend Logic         Backend (Future)
              - Modal control        - Booking storage
              - Form validation      - Payment gateway
              - User feedback        - Email sending


BROWSER EXECUTION:

1. User loads checkout.php
2. Modal HTML/CSS/JS injected
3. User fills form, clicks button
4. JS detects click, opens modal
5. User accepts agreement
6. JS validates form
7. JS submits data (when backend ready)
8. PHP/Backend receives and processes
9. Payment gateway initiated
10. Confirmation email sent
```

---

This visual architecture helps understand:
- User flow through the booking process
- Modal structure and components
- Cancellation fee logic
- Data handling
- State transitions
- Responsive design approach
- Integration points
