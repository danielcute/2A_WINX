<?php
/**
 * SINTA Payment Modal Component
 * Integrated with SINTA's custom design system
 * No Bootstrap dependency - uses SINTA's global.css modal styles
 */
?>

<!-- Payment Modal - GCash -->
<div id="paymentModalGCash" class="modal" style="display: none;">
    <div class="modal__content" style="max-width: 500px;">
        <div class="modal__header">
            <h2><i class="fas fa-mobile-alt"></i> GCash Payment</h2>
            <button type="button" onclick="closePaymentModal()" class="modal__close">×</button>
        </div>
        <div style="background: var(--primary-pale); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.9rem; font-weight: 600;">Amount to Pay:</p>
            <p style="margin: 0.5rem 0 0 0; font-size: 1.8rem; font-family: var(--serif); color: var(--primary);">₱<span id="amountDisplay">0.00</span></p>
        </div>
        <form id="gcashPaymentForm" onsubmit="processPaymentGCash(event);">
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Mobile Number</label>
                <input type="tel" name="mobile_number" placeholder="09XXXXXXXXX" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <p style="font-size: 0.8rem; color: var(--gray); background: var(--cream); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;"><i class="fas fa-info-circle"></i> You will be redirected to GCash to complete the payment securely.</p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="closePaymentModal()" class="btn btn--ghost" style="flex: 1; background: white; border: 1px solid var(--border);">Cancel</button>
                <button type="submit" class="btn btn--primary" style="flex: 1;"><i class="fas fa-lock"></i> Pay via GCash</button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Modal - PayMaya -->
<div id="paymentModalPayMaya" class="modal" style="display: none;">
    <div class="modal__content" style="max-width: 500px;">
        <div class="modal__header">
            <h2><i class="fas fa-wallet"></i> PayMaya Payment</h2>
            <button type="button" onclick="closePaymentModal()" class="modal__close">×</button>
        </div>
        <div style="background: var(--primary-pale); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.9rem; font-weight: 600;">Amount to Pay:</p>
            <p style="margin: 0.5rem 0 0 0; font-size: 1.8rem; font-family: var(--serif); color: var(--primary);">₱<span id="amountDisplayPayMaya">0.00</span></p>
        </div>
        <form id="paymayaPaymentForm" onsubmit="processPaymentPayMaya(event);">
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Mobile Number</label>
                <input type="tel" name="mobile_number" placeholder="09XXXXXXXXX" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <p style="font-size: 0.8rem; color: var(--gray); background: var(--cream); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;"><i class="fas fa-lock"></i> Secure payment powered by PayMaya.</p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="closePaymentModal()" class="btn btn--ghost" style="flex: 1; background: white; border: 1px solid var(--border);">Cancel</button>
                <button type="submit" class="btn btn--primary" style="flex: 1;"><i class="fas fa-lock"></i> Pay via PayMaya</button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Modal - Bank Transfer -->
<div id="paymentModalBank" class="modal" style="display: none;">
    <div class="modal__content" style="max-width: 500px;">
        <div class="modal__header">
            <h2><i class="fas fa-university"></i> Bank Transfer</h2>
            <button type="button" onclick="closePaymentModal()" class="modal__close">×</button>
        </div>
        <div style="background: var(--primary-pale); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.9rem; font-weight: 600;">Amount to Pay:</p>
            <p style="margin: 0.5rem 0 0 0; font-size: 1.8rem; font-family: var(--serif); color: var(--primary);">₱<span id="amountDisplayBank">0.00</span></p>
        </div>
        <form id="bankPaymentForm" onsubmit="processPaymentBank(event);">
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Bank Name</label>
                <input type="text" name="bank_name" placeholder="e.g., BDO, BPI, Metrobank" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Account Number</label>
                <input type="text" name="account_number" placeholder="Your bank account number" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.85rem; font-weight: 600;">Account Holder</label>
                <input type="text" name="account_holder" placeholder="Name on the account" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--sans);">
            </div>
            <p style="font-size: 0.8rem; color: var(--gray); background: var(--primary-pale); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;"><i class="fas fa-info-circle"></i> A reference number will be provided for your transfer.</p>
            <div style="display: flex; gap: 1rem;">
                <button type="button" onclick="closePaymentModal()" class="btn btn--ghost" style="flex: 1; background: white; border: 1px solid var(--border);">Cancel</button>
                <button type="submit" class="btn btn--primary" style="flex: 1;"><i class="fas fa-paper-plane"></i> Proceed</button>
            </div>
        </form>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="modal" style="display: none;">
    <div class="modal__content" style="max-width: 600px;">
        <div class="modal__icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Payment <em>Successful!</em></h2>
        <div id="receiptContent" style="background: var(--cream); padding: 1.5rem; border-radius: var(--radius-md); margin: 1.5rem 0; text-align: left;">
            <!-- Receipt content will be loaded here -->
        </div>
        <div class="modal__actions">
            <a href="/index.php?route=plans" class="btn btn--primary">View My Bookings</a>
            <a href="/index.php?route=homepage" class="btn btn--ghost">Go Home</a>
        </div>
    </div>
</div>

<script>
// Store current payment data
let currentPaymentModalData = {
    planId: null,
    amount: 0,
    paymentType: 'deposit'
};

/**
 * Open payment modal for new payment
 */
function openPaymentModal(planId, paymentType = 'deposit', amount = 0) {
    currentPaymentModalData.planId = planId;
    currentPaymentModalData.amount = amount;
    currentPaymentModalData.paymentType = paymentType;
    
    const formattedAmount = parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    // Update amount displays
    document.getElementById('amountDisplay').textContent = formattedAmount;
    document.getElementById('amountDisplayPayMaya').textContent = formattedAmount;
    document.getElementById('amountDisplayBank').textContent = formattedAmount;
    
    console.log('Opening payment modal for plan:', planId, 'amount:', amount, 'type:', paymentType);
    
    // Show GCash modal by default
    document.getElementById('paymentModalGCash').style.display = 'flex';
}

/**
 * Close payment modal
 */
function closePaymentModal() {
    document.getElementById('paymentModalGCash').style.display = 'none';
    document.getElementById('paymentModalPayMaya').style.display = 'none';
    document.getElementById('paymentModalBank').style.display = 'none';
    document.getElementById('receiptModal').style.display = 'none';
}

/**
 * Switch between payment modals
 */
function switchPaymentMethod(method) {
    closePaymentModal();
    const modalId = 'paymentModal' + method;
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

/**
 * Process GCash Payment
 */
function processPaymentGCash(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    formData.append('action', 'process_payment');
    formData.append('plan_id', currentPaymentModalData.planId);
    formData.append('payment_method', 'gcash');
    formData.append('payment_type', currentPaymentModalData.paymentType);
    
    console.log('Processing GCash payment...');
    showProcessingState(form, true);
    
    fetch('/api-payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log('Payment response:', data);
        showProcessingState(form, false);
        
        if (data.success) {
            if (data.checkout_url) {
                alert('Redirecting to GCash...');
                window.location.href = data.checkout_url;
            } else {
                showPaymentReceipt(data.receipt_id);
            }
        } else {
            alert('Payment Error: ' + (data.error || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Payment error:', error);
        showProcessingState(form, false);
        alert('Error processing payment: ' + error.message);
    });
}

/**
 * Process PayMaya Payment
 */
function processPaymentPayMaya(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    formData.append('action', 'process_payment');
    formData.append('plan_id', currentPaymentModalData.planId);
    formData.append('payment_method', 'paymaya');
    formData.append('payment_type', currentPaymentModalData.paymentType);
    
    showProcessingState(form, true);
    
    fetch('/api-payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showProcessingState(form, false);
        
        if (data.success) {
            if (data.checkout_url) {
                alert('Redirecting to PayMaya...');
                window.location.href = data.checkout_url;
            } else {
                showPaymentReceipt(data.receipt_id);
            }
        } else {
            alert('Payment Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        showProcessingState(form, false);
        alert('Error: ' + error.message);
    });
}

/**
 * Process Bank Transfer
 */
function processPaymentBank(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    formData.append('action', 'process_payment');
    formData.append('plan_id', currentPaymentModalData.planId);
    formData.append('payment_method', 'bank_transfer');
    formData.append('payment_type', currentPaymentModalData.paymentType);
    
    showProcessingState(form, true);
    
    fetch('/api-payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showProcessingState(form, false);
        
        if (data.success) {
            showPaymentReceipt(data.receipt_id);
        } else {
            alert('Error: ' + (data.error || 'Payment failed'));
        }
    })
    .catch(error => {
        showProcessingState(form, false);
        alert('Error: ' + error.message);
    });
}

/**
 * Show receipt after successful payment
 */
function showPaymentReceipt(receiptId) {
    fetch('/api-payment.php?action=get_receipts&receipt_id=' + receiptId)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.receipts && data.receipts.length > 0) {
            const receipt = data.receipts[0];
            const html = generateReceiptDisplay(receipt);
            document.getElementById('receiptContent').innerHTML = html;
            closePaymentModal();
            document.getElementById('receiptModal').style.display = 'flex';
        }
    })
    .catch(error => console.error('Error loading receipt:', error));
}

/**
 * Generate receipt HTML for display
 */
function generateReceiptDisplay(receipt) {
    const balanceRemaining = parseFloat(receipt.balance_remaining || 0);
    return `
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.9rem; color: var(--gray);">Receipt #${receipt.receipt_number}</p>
            <p style="margin: 0.5rem 0 0 0; font-size: 1.2rem; font-weight: 600; color: var(--dark);">${receipt.customer_name}</p>
        </div>
        
        <div style="border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 1rem 0; margin: 1rem 0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.85rem;">
                <div>
                    <p style="margin: 0; color: var(--gray); font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Event</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 600;">${receipt.event_name || 'Event Booking'}</p>
                </div>
                <div>
                    <p style="margin: 0; color: var(--gray); font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Date</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 600;">${receipt.event_date ? new Date(receipt.event_date).toLocaleDateString() : 'TBD'}</p>
                </div>
            </div>
        </div>
        
        <div style="margin: 1rem 0;">
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.9rem;">
                <span>Amount Paid:</span>
                <strong style="color: var(--success);">₱${parseFloat(receipt.amount_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong>
            </div>
            ${balanceRemaining > 0 ? `
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.9rem;">
                <span>Balance Due:</span>
                <strong style="color: var(--primary);">₱${balanceRemaining.toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong>
            </div>
            ` : ''}
            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-top: 1px solid var(--border); margin-top: 0.5rem; font-size: 1rem; font-weight: 600;">
                <span>Payment Method:</span>
                <strong>${(receipt.payment_method || 'N/A').toUpperCase().replace('_', ' ')}</strong>
            </div>
        </div>
        
        ${balanceRemaining > 0 ? `
            <div style="background: var(--primary-pale); border-left: 4px solid var(--primary); padding: 0.75rem; margin-top: 1rem; border-radius: var(--radius-sm);">
                <p style="margin: 0; font-size: 0.85rem; color: var(--primary);"><i class="fas fa-info-circle"></i> Balance due 2 weeks before your event</p>
            </div>
        ` : `
            <div style="background: var(--success-pale); border-left: 4px solid var(--success); padding: 0.75rem; margin-top: 1rem; border-radius: var(--radius-sm);">
                <p style="margin: 0; font-size: 0.85rem; color: var(--success);"><i class="fas fa-check-circle"></i> Full payment received! Your booking is confirmed.</p>
            </div>
        `}
    `;
}

/**
 * Show processing state on button
 */
function showProcessingState(form, show) {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        if (show) {
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalText || 'Submit';
        }
    }
}

// Close modals when clicking outside (on the modal overlay)
document.addEventListener('click', function(e) {
    if (e.target.classList && e.target.classList.contains('modal')) {
        closePaymentModal();
    }
});
</script>

<style>
/* Modal styling for payment components */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal__content {
    background: white;
    border-radius: var(--radius-lg);
    padding: 2rem;
    position: relative;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.modal__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal__header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-family: var(--serif);
}

.modal__close {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: var(--gray-light);
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal__close:hover {
    color: var(--dark);
}

.modal__icon {
    text-align: center;
    margin-bottom: 1rem;
}

.modal__icon i {
    font-size: 3rem;
    color: var(--success);
}

.modal__actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.modal__actions a {
    flex: 1;
    text-align: center;
}

/* Ensure modal content is visible and accessible */
.modal h2 {
    font-size: 1.5rem;
    margin: 0 0 1rem 0;
    color: var(--dark);
}

.modal em {
    font-style: italic;
    color: var(--primary);
}

@media (max-width: 600px) {
    .modal__content {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal__actions {
        flex-direction: column;
    }
}
</style>
