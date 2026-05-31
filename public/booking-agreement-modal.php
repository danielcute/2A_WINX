<!-- Booking Agreement Modal -->
<div id="bookingAgreementModal" class="agreement-modal">
    <div class="agreement-modal__overlay" onclick="closeAgreementModal()"></div>
    
    <div class="agreement-modal__content">
        <!-- Header -->
        <div class="agreement-modal__header">
            <h2><i class="fas fa-file-contract"></i> Booking Agreement</h2>
            <button type="button" class="agreement-modal__close" onclick="closeAgreementModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="agreement-modal__body">
            <!-- Introduction -->
            <section class="agreement-section">
                <h3>Terms & Conditions</h3>
                <p>Before confirming your booking with <strong>SINTA Event Planning</strong>, please carefully review and accept the following terms and conditions. By checking the box below, you acknowledge that you understand and agree to these terms.</p>
            </section>

            <!-- Booking Confirmation -->
            <section class="agreement-section">
                <h4><i class="fas fa-check-circle"></i> Booking Confirmation</h4>
                <ul class="agreement-list">
                    <li><strong>Non-Refundable Deposit:</strong> A 50% non-refundable deposit is required to confirm your booking.</li>
                    <li><strong>Confirmation:</strong> Your booking is confirmed only after payment of the deposit and acceptance of these terms.</li>
                    <li><strong>Balance Payment:</strong> The remaining 50% balance is due 2 weeks before the event date.</li>
                    <li><strong>Event Date:</strong> Once confirmed, the event date and time cannot be changed without mutual written agreement.</li>
                </ul>
            </section>

            <!-- Cancellation Policy -->
            <section class="agreement-section" style="border-left: 4px solid #dc3545; background: rgba(220, 53, 69, 0.05); padding: 1rem;">
                <h4><i class="fas fa-exclamation-circle" style="color: #dc3545;"></i> Cancellation Policy</h4>
                <p style="font-weight: 600; color: #dc3545; margin-bottom: 1rem;">If you decide to cancel your booking after confirmation, the following cancellation fees will apply:</p>
                
                <div class="cancellation-timeline">
                    <!-- More than 30 days -->
                    <div class="timeline-item">
                        <div class="timeline-label">
                            <strong>60+ days before event</strong>
                        </div>
                        <div class="timeline-content">
                            <p><strong>Cancellation Fee: ₱2,000</strong></p>
                            <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0 0;">Forfeit ₱2,000 from your deposit, remaining balance refunded.</p>
                        </div>
                    </div>

                    <!-- 30-60 days -->
                    <div class="timeline-item">
                        <div class="timeline-label">
                            <strong>30-59 days before event</strong>
                        </div>
                        <div class="timeline-content">
                            <p><strong>Cancellation Fee: ₱5,000</strong></p>
                            <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0 0;">Forfeit ₱5,000 from your deposit, remaining balance refunded.</p>
                        </div>
                    </div>

                    <!-- Less than 30 days -->
                    <div class="timeline-item">
                        <div class="timeline-label">
                            <strong>Less than 30 days before event</strong>
                        </div>
                        <div class="timeline-content">
                            <p><strong>Cancellation Fee: 100% (Full Forfeiture)</strong></p>
                            <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0 0;">Entire deposit is forfeited. No refunds issued.</p>
                        </div>
                    </div>
                </div>

                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                    <p style="margin: 0; font-size: 0.9rem; color: #856404;">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> Cancellation requests must be submitted in writing to our support team. Refunds (where applicable) will be processed within 7-10 business days.
                    </p>
                </div>
            </section>

            <!-- Rescheduling Policy -->
            <section class="agreement-section">
                <h4><i class="fas fa-calendar-check"></i> Rescheduling</h4>
                <ul class="agreement-list">
                    <li>Event dates can be rescheduled without penalty up to <strong>60 days</strong> before the event date.</li>
                    <li>Rescheduling within 30 days of the original date may incur a ₱3,000 rescheduling fee.</li>
                    <li>Rescheduling must be coordinated with our team based on availability.</li>
                </ul>
            </section>

            <!-- Payment Terms -->
            <section class="agreement-section">
                <h4><i class="fas fa-credit-card"></i> Payment Terms</h4>
                <ul class="agreement-list">
                    <li>Payment can be made via bank transfer, GCash, PayMaya, or credit card.</li>
                    <li>Late payment of the balance may result in event postponement or cancellation.</li>
                    <li>All prices quoted are in Philippine Peso (₱) and include 12% VAT.</li>
                    <li>Additional services not included in the package will be charged separately.</li>
                </ul>
            </section>

            <!-- Liability & Responsibility -->
            <section class="agreement-section">
                <h4><i class="fas fa-shield-alt"></i> Liability & Responsibility</h4>
                <ul class="agreement-list">
                    <li>SINTA Event Planning is not responsible for venue or third-party vendor cancellations due to force majeure (weather, natural disasters, government restrictions, etc.).</li>
                    <li>In case of such events, we will attempt to reschedule your event at no additional cost.</li>
                    <li>The client is responsible for informing us of any special requirements or venue restrictions in advance.</li>
                    <li>We are not liable for damages to personal belongings brought to the venue.</li>
                </ul>
            </section>

            <!-- Final Confirmation -->
            <section class="agreement-section" style="border-top: 2px solid #ddd; padding-top: 1.5rem;">
                <p style="font-size: 0.95rem; line-height: 1.6;">
                    By clicking "I Agree & Confirm Booking" below, I acknowledge that:
                </p>
                <ul class="agreement-list">
                    <li>I have read and understood all terms and conditions.</li>
                    <li>I understand the cancellation policy and associated fees.</li>
                    <li>I authorize the 50% deposit charge to proceed with booking confirmation.</li>
                    <li>I accept full responsibility for providing accurate contact and event information.</li>
                    <li>I agree to receive booking confirmations and event updates via email and phone.</li>
                </ul>
            </section>
        </div>

        <!-- Footer with Agreement Checkbox & Actions -->
        <div class="agreement-modal__footer">
            <div class="agreement-checkbox">
                <input type="checkbox" id="agreeTermsCheckbox" onchange="toggleAgreementButton()">
                <label for="agreeTermsCheckbox">
                    I have read, understood, and agree to all terms and the cancellation policy
                </label>
            </div>
            
            <div class="agreement-actions">
                <button type="button" class="btn btn--ghost" onclick="closeAgreementModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" id="agreeButton" class="btn btn--primary" disabled onclick="acceptAgreementAndProceed()">
                    <i class="fas fa-check"></i> I Agree & Confirm Booking
                </button>
            </div>

            <p style="font-size: 0.7rem; color: var(--gray-light); text-align: center; margin-top: 1rem; margin-bottom: 0;">
                Agreement effective from: <span id="agreementDate"></span>
            </p>
        </div>
    </div>
</div>

<!-- Hidden form to submit booking data after agreement acceptance -->
<form id="bookingForm" method="POST" action="index.php?route=checkout-submit" style="display: none;">
    <input type="hidden" name="eventName" id="bookingEventName">
    <input type="hidden" name="eventDate" id="bookingEventDate">
    <input type="hidden" name="eventTime" id="bookingEventTime">
    <input type="hidden" name="guestCount" id="bookingGuestCount">
    <input type="hidden" name="venueLocation" id="bookingVenueLocation">
    <input type="hidden" name="specialRequests" id="bookingSpecialRequests">
    <input type="hidden" name="fullName" id="bookingFullName">
    <input type="hidden" name="email" id="bookingEmail">
    <input type="hidden" name="phone" id="bookingPhone">
    <input type="hidden" name="contactMethod" id="bookingContactMethod">
    <input type="hidden" name="cart_data" id="bookingCartData">
    <input type="hidden" name="latitude" id="bookingLatitude">
    <input type="hidden" name="longitude" id="bookingLongitude">
    <input type="hidden" name="agreeTerms" value="1">
</form>

<style>
    /* Agreement Modal Styles */
    .agreement-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .agreement-modal.active {
        opacity: 1;
        visibility: visible;
    }

    .agreement-modal__overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        cursor: pointer;
    }

    .agreement-modal__content {
        position: relative;
        background: white;
        border-radius: var(--radius-xl);
        max-width: 600px;
        width: 90%;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .agreement-modal__header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e5e5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .agreement-modal__header h2 {
        margin: 0;
        font-family: var(--serif);
        font-size: 1.4rem;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .agreement-modal__header h2 i {
        color: var(--primary);
        font-size: 1.3rem;
    }

    .agreement-modal__close {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.5rem;
        color: var(--gray);
        transition: all 0.2s ease;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .agreement-modal__close:hover {
        color: var(--dark);
        background: var(--cream);
        border-radius: 50%;
    }

    .agreement-modal__body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        padding-right: 1rem; /* Space for scrollbar */
    }

    .agreement-section {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e5e5;
    }

    .agreement-section:last-of-type {
        border-bottom: none;
        margin-bottom: 1rem;
        padding-bottom: 0;
    }

    .agreement-section h3 {
        font-family: var(--serif);
        font-size: 1.2rem;
        color: var(--dark);
        margin: 0 0 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary);
    }

    .agreement-section h4 {
        font-family: var(--sans);
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--dark);
        margin: 0 0 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .agreement-section h4 i {
        color: var(--primary);
        font-size: 1rem;
    }

    .agreement-section p {
        margin: 0 0 1rem;
        font-size: 0.9rem;
        line-height: 1.5;
        color: var(--gray);
    }

    .agreement-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .agreement-list li {
        padding: 0.5rem 0 0.5rem 1.5rem;
        font-size: 0.9rem;
        line-height: 1.5;
        color: var(--gray);
        position: relative;
    }

    .agreement-list li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: var(--primary);
        font-weight: bold;
    }

    .agreement-list li strong {
        color: var(--dark);
    }

    /* Cancellation Timeline */
    .cancellation-timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin: 1rem 0;
    }

    .timeline-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .timeline-item:hover {
        background: #f9f7f3;
        border-color: var(--primary);
    }

    .timeline-label {
        flex: 0 0 130px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
        padding: 0.25rem 0;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-content p:first-child {
        margin: 0;
        font-weight: 600;
        color: var(--dark);
    }

    .timeline-content p:last-child {
        margin-bottom: 0;
    }

    /* Modal Footer */
    .agreement-modal__footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e5e5;
        background: var(--cream);
        border-radius: 0 0 var(--radius-xl) var(--radius-xl);
        flex-shrink: 0;
    }

    .agreement-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .agreement-checkbox input[type="checkbox"] {
        margin-top: 2px;
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .agreement-checkbox label {
        cursor: pointer;
        font-size: 0.9rem;
        line-height: 1.4;
        color: var(--dark);
        flex: 1;
    }

    .agreement-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .agreement-actions .btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.85rem;
        min-width: 140px;
    }

    .btn--ghost {
        background: white;
        border: 1px solid #ddd;
        color: var(--dark);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn--ghost:hover:not(:disabled) {
        background: #f5f5f5;
        border-color: var(--primary);
        color: var(--primary);
    }

    #agreeButton:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 600px) {
        .agreement-modal__content {
            max-height: 90vh;
            width: 95%;
            border-radius: var(--radius-lg);
        }

        .agreement-modal__body {
            padding: 1rem;
            padding-right: 0.75rem;
        }

        .agreement-section {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
        }

        .agreement-section h3 {
            font-size: 1.1rem;
        }

        .agreement-section h4 {
            font-size: 0.9rem;
        }

        .agreement-list li {
            font-size: 0.85rem;
        }

        .agreement-section p {
            font-size: 0.85rem;
        }

        .timeline-label {
            flex: 0 0 100px;
            font-size: 0.8rem;
        }

        .agreement-actions {
            flex-direction: column;
        }

        .agreement-actions .btn {
            width: 100%;
            min-width: auto;
        }

        .agreement-modal__header {
            padding: 1rem;
        }

        .agreement-modal__header h2 {
            font-size: 1.2rem;
        }

        .agreement-modal__footer {
            padding: 1rem;
        }
    }

    @media (max-width: 480px) {
        .agreement-modal__content {
            width: 98%;
        }

        .agreement-section h3 {
            font-size: 1rem;
        }

        .agreement-section h4 {
            font-size: 0.85rem;
        }

        .agreement-list li {
            font-size: 0.8rem;
            padding: 0.4rem 0 0.4rem 1.2rem;
        }

        .agreement-section p {
            font-size: 0.8rem;
        }

        .agreement-checkbox label {
            font-size: 0.8rem;
        }
    }
</style>

<script>
    // Initialize agreement date
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const dateStr = today.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById('agreementDate').textContent = dateStr;
    });

    // Open agreement modal
    function openAgreementModal() {
        document.getElementById('bookingAgreementModal').classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }

    // Close agreement modal
    function closeAgreementModal() {
        document.getElementById('bookingAgreementModal').classList.remove('active');
        document.getElementById('agreeTermsCheckbox').checked = false;
        toggleAgreementButton();
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }

    // Toggle agreement button enabled/disabled
    function toggleAgreementButton() {
        const checkbox = document.getElementById('agreeTermsCheckbox');
        const button = document.getElementById('agreeButton');
        button.disabled = !checkbox.checked;
    }

    // Accept agreement and proceed with booking
    function acceptAgreementAndProceed() {
        // Validate that essential booking information is filled
        const eventDate = document.getElementById('eventDate').value;
        const eventTime = document.getElementById('eventTime').value;
        const fullName = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();

        // Basic validation
        if (!eventDate || !eventTime) {
            showToast('Please select an event date and time', 'error');
            return;
        }

        if (!fullName || !email || !phone) {
            showToast('Please fill in all required contact information', 'error');
            return;
        }

        if (!email.includes('@')) {
            showToast('Please enter a valid email address', 'error');
            return;
        }

        // Populate booking form with data
        document.getElementById('bookingEventName').value = document.getElementById('eventName').value || 'Event';
        document.getElementById('bookingEventDate').value = eventDate;
        document.getElementById('bookingEventTime').value = eventTime;
        document.getElementById('bookingGuestCount').value = document.getElementById('guestCount').value || '0';
        document.getElementById('bookingVenueLocation').value = document.getElementById('venueLocation').value || '';
        document.getElementById('bookingSpecialRequests').value = document.getElementById('specialRequests').value || '';
        document.getElementById('bookingFullName').value = fullName;
        document.getElementById('bookingEmail').value = email;
        document.getElementById('bookingPhone').value = phone;
        document.getElementById('bookingContactMethod').value = document.getElementById('contactMethod').value;
        document.getElementById('bookingLatitude').value = document.getElementById('latitude').value;
        document.getElementById('bookingLongitude').value = document.getElementById('longitude').value;

        // Get cart data - reconstruct from cart items on page
        const cartItems = [];
        document.querySelectorAll('.cart-item').forEach(item => {
            const nameEl = item.querySelector('.cart-item__name');
            const priceEl = item.querySelector('.cart-item__price');
            if (nameEl && priceEl) {
                cartItems.push({
                    name: nameEl.textContent,
                    price: parseInt(priceEl.textContent.replace(/[^\d]/g, '')) || 0,
                    type: item.querySelector('.cart-item__type').textContent.includes('Custom') ? 'custom' : 'package'
                });
            }
        });
        document.getElementById('bookingCartData').value = JSON.stringify(cartItems);

        // Close modal
        closeAgreementModal();

        // Show confirmation and proceed to payment
        showToast('Agreement accepted! Processing your booking...', 'success');

        // Proceed to payment after a brief delay
        setTimeout(() => {
            proceedToPayment();
        }, 1500);
    }

    // Proceed to payment processing (this will be called after agreement is accepted)
    function proceedToPayment() {
        // This function will be called to process the payment
        // You can customize this based on your payment flow
        const cartTotal = document.querySelector('.breakdown-total span:last-child')?.textContent || '0';
        const depositAmount = parseInt(cartTotal.replace(/[^\d]/g, '')) / 2;

        // Show payment processing message
        showToast('Redirecting to payment gateway...', 'info');

        // Here you would submit the booking data and initiate payment
        // For now, we'll log the data
        console.log('Booking confirmed and ready for payment');
        console.log('Deposit amount:', depositAmount);
        
        document.getElementById('bookingForm').submit();
    }

    // Toast notification helper
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Modified confirmBooking function to open agreement first
    function confirmBooking() {
        // Validate form data before opening agreement
        const eventDate = document.getElementById('eventDate').value;
        const eventTime = document.getElementById('eventTime').value;
        const fullName = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!eventDate || !eventTime) {
            showToast('Please select an event date and time', 'error');
            return;
        }

        if (!fullName || !email) {
            showToast('Please fill in your contact information', 'error');
            return;
        }

        // Open the agreement modal instead of directly confirming
        openAgreementModal();
    }
</script>
