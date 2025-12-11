<?php
/**
 * Live Booking Modal
 * Shared component for booking live and scheduled streams.
 */

$booking_nonce = wp_create_nonce('nymia_book_live_stream');
$ajax_url      = admin_url('admin-ajax.php');
$login_url     = wp_login_url();
$is_logged_in  = is_user_logged_in();
?>

<?php if (!defined('ABSPATH')) { exit; } ?>

<div id="nymia-booking-overlay" class="nymia-booking-overlay"></div>
<div id="nymia-booking-modal" class="nymia-booking-modal">
    <div class="nymia-booking-modal-content">
        <button id="nymia-booking-close" class="nymia-booking-close" aria-label="<?php esc_attr_e('Close', 'nymia'); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div class="nymia-booking-header">
            <h2><?php esc_html_e('Book Live Session', 'nymia'); ?></h2>
            <p class="nymia-booking-subtitle" id="booking_stream_title"><?php esc_html_e('Stream Title', 'nymia'); ?></p>
            <p class="nymia-booking-remaining" id="booking_remaining_slots" style="display:none;"></p>
        </div>
        
        <form id="nymia-booking-form" class="nymia-booking-form">
            <div class="nymia-booking-options">
                <div class="nymia-booking-option">
                    <label class="nymia-booking-option-label">
                        <input type="radio" name="payment_type" value="full" checked />
                        <div class="nymia-booking-option-content">
                            <div class="nymia-booking-option-header">
                                <span class="nymia-booking-option-title"><?php esc_html_e('Full Session', 'nymia'); ?></span>
                                <span class="nymia-booking-option-price" id="booking_full_price">$0.00</span>
                            </div>
                            <p class="nymia-booking-option-desc"><?php esc_html_e('Access to the entire live session', 'nymia'); ?></p>
                        </div>
                    </label>
                </div>
                
                <div class="nymia-booking-option" data-booking-per-minute>
                    <label class="nymia-booking-option-label">
                        <input type="radio" name="payment_type" value="per_minute" />
                        <div class="nymia-booking-option-content">
                            <div class="nymia-booking-option-header">
                                <span class="nymia-booking-option-title"><?php esc_html_e('Pay Per Minute', 'nymia'); ?></span>
                                <span class="nymia-booking-option-price" id="booking_per_minute_price">$0.00 per minute</span>
                            </div>
                            <p class="nymia-booking-option-desc"><?php esc_html_e('Pay only for the time you watch', 'nymia'); ?></p>
                            <div class="nymia-booking-minutes-selector">
                                <label for="booking_minutes"><?php esc_html_e('How many minutes?', 'nymia'); ?></label>
                                <input type="number" id="booking_minutes" name="minutes" min="1" max="480" value="10" />
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="nymia-form-group" id="booking_reminder_wrapper" style="margin: 12px 0;">
                <label for="booking_reminder" style="display:block;margin-bottom:6px;font-size:0.9rem;color:rgba(255,255,255,0.8);">
                    <?php esc_html_e('Reminder', 'nymia'); ?>
                </label>
                <select id="booking_reminder" name="reminder_minutes" class="nymia-select" style="width:100%;">
                    <option value=""><?php esc_html_e('No reminder', 'nymia'); ?></option>
                    <option value="5"><?php esc_html_e('5 minutes before', 'nymia'); ?></option>
                    <option value="15" selected><?php esc_html_e('15 minutes before', 'nymia'); ?></option>
                    <option value="30"><?php esc_html_e('30 minutes before', 'nymia'); ?></option>
                    <option value="60"><?php esc_html_e('1 hour before', 'nymia'); ?></option>
                    <option value="120"><?php esc_html_e('2 hours before', 'nymia'); ?></option>
                </select>
                <small style="display:block;margin-top:6px;color:rgba(255,255,255,0.6);">
                    <?php esc_html_e('We’ll remind you before the scheduled start time.', 'nymia'); ?>
                </small>
            </div>
            
            <div class="nymia-booking-total">
                <div class="nymia-booking-total-label">
                    <span><?php esc_html_e('Total Amount', 'nymia'); ?></span>
                    <span class="nymia-booking-total-price" id="booking_total_price">$0.00</span>
                </div>
            </div>
            
            <div class="nymia-booking-actions">
                <button type="button" class="nymia-booking-cancel" data-booking-cancel>
                    <?php esc_html_e('Cancel', 'nymia'); ?>
                </button>
                <button type="submit" class="nymia-booking-submit nymia-btn-gradient">
                    <?php esc_html_e('Proceed to Payment', 'nymia'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Form Modal (shown after clicking "Proceed to Payment") -->
<div id="nymia-payment-modal" class="nymia-payment-modal">
    <div class="nymia-payment-modal-content">
        <button id="nymia-payment-close" class="nymia-booking-close" aria-label="<?php esc_attr_e('Close', 'nymia'); ?>" style="position: absolute; top: 16px; right: 16px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div class="nymia-payment-header">
            <h3><?php esc_html_e('Complete Payment', 'nymia'); ?></h3>
            <p style="color: rgba(255,255,255,0.7); margin-top: 8px;">
                <span id="payment-item-title"></span> - <span id="payment-amount"></span>
            </p>
        </div>
        
        <form id="nymia-payment-form">
            <div id="stripe-payment-element" style="margin: 24px 0;">
                <!-- Stripe Elements will be mounted here -->
            </div>
            
            <div id="payment-error-message" style="display: none; padding: 12px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; color: #ef4444; margin-bottom: 16px;"></div>
            
            <div class="nymia-booking-actions">
                <button type="button" id="payment-cancel-btn" class="nymia-booking-cancel">
                    <?php esc_html_e('Cancel', 'nymia'); ?>
                </button>
                <button type="submit" id="payment-submit-btn" class="nymia-booking-submit nymia-btn-gradient">
                    <?php esc_html_e('Pay Now', 'nymia'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
(function(){
    if (window.nymiaBookingModalInitialized) {
        return;
    }
    window.nymiaBookingModalInitialized = true;

    const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    const loginUrl = <?php echo wp_json_encode($login_url); ?>;
    const ajaxUrl = (window.nymiaAjax && window.nymiaAjax.ajaxurl) || <?php echo wp_json_encode($ajax_url); ?>;
    const bookingNonce = <?php echo wp_json_encode($booking_nonce); ?>;

    const bookingModal = document.getElementById('nymia-booking-modal');
    const bookingOverlay = document.getElementById('nymia-booking-overlay');
    const bookingModalContent = document.querySelector('.nymia-booking-modal-content');
    const bookingCloseBtn = document.getElementById('nymia-booking-close');
    const bookingForm = document.getElementById('nymia-booking-form');
    const paymentTypeRadios = bookingForm ? bookingForm.querySelectorAll('input[name="payment_type"]') : [];
    const minutesInput = document.getElementById('booking_minutes');
    const totalPriceDisplay = document.getElementById('booking_total_price');
    const fullPriceDisplay = document.getElementById('booking_full_price');
    const perMinutePriceDisplay = document.getElementById('booking_per_minute_price');
    const perMinuteOption = bookingForm ? bookingForm.querySelector('[data-booking-per-minute]') : null;
    const cancelBtn = bookingForm ? bookingForm.querySelector('[data-booking-cancel]') : null;
    const submitBtn = bookingForm ? bookingForm.querySelector('button[type="submit"]') : null;
    const remainingSlotsEl = document.getElementById('booking_remaining_slots');
    const reminderWrapper = document.getElementById('booking_reminder_wrapper');
    const reminderSelect = document.getElementById('booking_reminder');

    const state = {
        room: {
            booking_type: 'live',
            room_id: '',
            slot_id: '',
            creator_id: '',
            stream_title: '',
            stream_price: 0,
            per_minute_price: 0,
            is_scheduled: false,
            start_timestamp: 0,
            is_event: false,
            event_type: 'single',
            max_attendees: 0,
            current_attendees: 0,
            reminder_minutes: 0
        }
    };

    function closeModal() {
        hidePaymentModal(); // Close payment modal if open
        if (bookingModal) bookingModal.classList.remove('is-visible');
        if (bookingOverlay) bookingOverlay.classList.remove('is-visible');
    }

    function formatCurrency(value) {
        return '$' + (Number(value) || 0).toFixed(2);
    }

    function shouldHidePerMinute() {
        return state.room.is_event || state.room.booking_type === 'private';
    }

    function togglePerMinuteOption(show) {
        if (!perMinuteOption) {
            return;
        }
        const visible = show && !shouldHidePerMinute();
        perMinuteOption.style.display = visible ? '' : 'none';
        if (!visible) {
            const fullRadio = bookingForm.querySelector('input[name="payment_type"][value="full"]');
            if (fullRadio) {
                fullRadio.checked = true;
            }
        }
    }

    function updateMinutesState() {
        if (!minutesInput) return;
        const selectedType = bookingForm.querySelector('input[name="payment_type"]:checked')?.value || 'full';
        const disableInput = selectedType === 'full' || shouldHidePerMinute();
        minutesInput.readOnly = disableInput;
        if (disableInput) {
            minutesInput.value = '10';
        }
    }

    function updateRemainingSlots() {
        if (!remainingSlotsEl) {
            return;
        }
        if (state.room.is_event && state.room.max_attendees > 0) {
            const total = state.room.max_attendees;
            const remaining = Math.max(0, total - state.room.current_attendees);
            const label = remaining === 1
                ? '<?php echo esc_js(__('Remaining Slot', 'nymia')); ?>'
                : '<?php echo esc_js(__('Remaining Slots', 'nymia')); ?>';
            remainingSlotsEl.textContent = `${label}: ${remaining} / ${total}`;
            remainingSlotsEl.style.display = '';
        } else {
            remainingSlotsEl.style.display = 'none';
            remainingSlotsEl.textContent = '';
        }
    }

    function updateReminderVisibility() {
        if (!reminderWrapper || !reminderSelect) {
            return;
        }
        if (state.room.is_scheduled) {
            reminderWrapper.style.display = '';
            reminderSelect.disabled = false;
            reminderSelect.value = reminderSelect.value || '15';
        } else {
            reminderWrapper.style.display = 'none';
            reminderSelect.disabled = true;
            reminderSelect.value = '';
        }
    }

    function updateTotalPrice() {
        if (!totalPriceDisplay) return;
        const selectedType = bookingForm.querySelector('input[name="payment_type"]:checked')?.value || 'full';
        let total = 0;

        if (selectedType === 'full') {
            total = state.room.stream_price || 0;
        } else {
            const minutes = parseInt(minutesInput.value, 10) || 0;
            total = (state.room.per_minute_price || 0) * minutes;
        }

        totalPriceDisplay.textContent = formatCurrency(total);
    }

    function openModal(roomData) {
        if (!roomData) {
            return;
        }

        if (!isLoggedIn) {
            // Show login modal popup instead of redirecting
            if (typeof window.openLoginModal === 'function') {
                window.openLoginModal('login');
            } else {
                // Fallback to redirect if modal function is not available
            window.location.href = loginUrl;
            }
            return;
        }

        state.room = Object.assign({
            booking_type: 'live',
            room_id: '',
            slot_id: '',
            creator_id: '',
            stream_title: '',
            stream_price: 0,
            per_minute_price: 0,
            is_scheduled: false,
            start_timestamp: 0,
            is_event: false,
            event_type: 'single',
            max_attendees: 0,
            current_attendees: 0,
            reminder_minutes: 0
        }, roomData);
        state.room.booking_type = roomData.booking_type || (roomData.slot_id ? 'private' : 'live');

        if (fullPriceDisplay) {
            fullPriceDisplay.textContent = formatCurrency(state.room.stream_price);
        }
        if (perMinutePriceDisplay) {
            perMinutePriceDisplay.textContent = formatCurrency(state.room.per_minute_price) + ' <?php esc_html_e('per minute', 'nymia'); ?>';
        }

        if (state.room.stream_title) {
            document.getElementById('booking_stream_title').textContent = state.room.stream_title;
        }

        togglePerMinuteOption(true);
        updateRemainingSlots();
        updateReminderVisibility();
        updateMinutesState();
        updateTotalPrice();

        if (bookingModal) bookingModal.classList.add('is-visible');
        if (bookingOverlay) bookingOverlay.classList.add('is-visible');
    }

    // Payment modal elements
    const paymentModal = document.getElementById('nymia-payment-modal');
    const paymentCloseBtn = document.getElementById('nymia-payment-close');
    const paymentForm = document.getElementById('nymia-payment-form');
    const paymentElementContainer = document.getElementById('stripe-payment-element');
    const paymentErrorEl = document.getElementById('payment-error-message');
    const paymentSubmitBtn = document.getElementById('payment-submit-btn');
    const paymentCancelBtn = document.getElementById('payment-cancel-btn');
    const paymentItemTitle = document.getElementById('payment-item-title');
    const paymentAmount = document.getElementById('payment-amount');
    
    let stripe = null;
    let elements = null;
    let paymentElement = null;
    let currentPaymentIntent = null;

    function formatDateTime(timestamp) {
        const date = new Date(timestamp * 1000);
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        };
        return date.toLocaleDateString('en-US', options);
    }

    function formatCountdown(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = timestamp - now;
        
        if (diff <= 0) {
            return '<?php echo esc_js(__('Available Now', 'nymia')); ?>';
        }
        
        const days = Math.floor(diff / 86400);
        const hours = Math.floor((diff % 86400) / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;
        
        if (days > 0) {
            return `${days}d ${hours}h ${minutes}m`;
        } else if (hours > 0) {
            return `${hours}h ${minutes}m`;
        } else if (minutes > 0) {
            return `${minutes}m ${seconds}s`;
        } else {
            return `${seconds}s`;
        }
    }

    function showScheduledBookingSuccess(streamTitle, startTimestamp, roomId) {
        // Create success message modal
        const successModal = document.createElement('div');
        successModal.id = 'nymia-booking-success-modal';
        successModal.className = 'nymia-booking-modal';
        successModal.style.display = 'block';
        successModal.innerHTML = `
            <div class="nymia-booking-modal-content" style="text-align: center;">
                <div style="margin-bottom: 24px;">
                    <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: linear-gradient(145deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h2 style="color: #fff; margin: 0 0 8px 0; font-size: 1.5rem;"><?php echo esc_js(__('Booking Successful!', 'nymia')); ?></h2>
                    <p style="color: rgba(255,255,255,0.7); margin: 0;">${streamTitle || '<?php echo esc_js(__('Live Session', 'nymia')); ?>'}</p>
                </div>
                
                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; margin: 24px 0;">
                    <p style="color: rgba(255,255,255,0.9); margin: 0 0 12px 0; font-size: 0.95rem;">
                        <?php echo esc_js(__('Your slot has been booked successfully!', 'nymia')); ?>
                    </p>
                    <p style="color: rgba(255,255,255,0.7); margin: 0 0 8px 0; font-size: 0.9rem;">
                        <strong style="color: #fff;"><?php echo esc_js(__('Scheduled Date:', 'nymia')); ?></strong><br>
                        <span id="success-scheduled-date" style="display: block; margin-top: 4px;"></span>
                    </p>
                    <p style="color: rgba(255,255,255,0.7); margin: 8px 0 0 0; font-size: 0.85rem;">
                        <strong style="color: #fff;"><?php echo esc_js(__('Time Remaining:', 'nymia')); ?></strong><br>
                        <span id="success-countdown" style="color: #10b981; font-weight: 600; display: block; margin-top: 4px;"></span>
                    </p>
                </div>
                
                <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin: 20px 0;">
                    <?php echo esc_js(__('You will be able to join the live stream when the scheduled time starts.', 'nymia')); ?>
                </p>
                
                <div class="nymia-booking-actions" style="margin-top: 24px;">
                    <button type="button" id="success-close-btn" class="nymia-booking-submit nymia-btn-gradient" style="width: 100%;">
                        <?php echo esc_js(__('Got it', 'nymia')); ?>
                    </button>
                </div>
            </div>
        `;
        
        // Add to body
        document.body.appendChild(successModal);
        
        // Show overlay
        if (bookingOverlay) {
            bookingOverlay.classList.add('is-visible');
        }
        successModal.classList.add('is-visible');
        
        // Update countdown
        const countdownEl = document.getElementById('success-countdown');
        const scheduledDateEl = document.getElementById('success-scheduled-date');
        
        if (scheduledDateEl) {
            scheduledDateEl.textContent = formatDateTime(startTimestamp);
        }
        
        function updateCountdown() {
            if (countdownEl) {
                const countdown = formatCountdown(startTimestamp);
                countdownEl.textContent = countdown;
                
                // If time has passed, show "Available Now"
                const now = Math.floor(Date.now() / 1000);
                if (startTimestamp <= now) {
                    countdownEl.textContent = '<?php echo esc_js(__('Available Now - You can join!', 'nymia')); ?>';
                    countdownEl.style.color = '#10b981';
                }
            }
        }
        
        // Update immediately and then every second
        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
        
        // Close button handler
        const closeBtn = document.getElementById('success-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                clearInterval(countdownInterval);
                successModal.remove();
                if (bookingOverlay) {
                    bookingOverlay.classList.remove('is-visible');
                }
                closeModal();
                // Reload to show updated booking status
                window.location.reload();
            });
        }
        
        // Close on overlay click
        if (bookingOverlay) {
            const overlayHandler = function() {
                clearInterval(countdownInterval);
                successModal.remove();
                bookingOverlay.classList.remove('is-visible');
                closeModal();
                window.location.reload();
                bookingOverlay.removeEventListener('click', overlayHandler);
            };
            bookingOverlay.addEventListener('click', overlayHandler);
        }
    }

    function showPaymentModal(amount, currency, itemTitle) {
        if (!paymentModal || !paymentItemTitle || !paymentAmount) return;
        
        paymentItemTitle.textContent = itemTitle || state.room.stream_title || '';
        paymentAmount.textContent = formatCurrency(amount);
        
        // Hide entire booking modal content, show payment form
        // Keep overlay visible for payment modal
        if (bookingModalContent) bookingModalContent.style.display = 'none';
        if (bookingModal) bookingModal.style.display = 'none';
        if (paymentModal) {
            paymentModal.classList.add('is-visible');
        }
        // Ensure overlay stays visible
        if (bookingOverlay) bookingOverlay.classList.add('is-visible');
    }

    function hidePaymentModal() {
        if (paymentModal) paymentModal.classList.remove('is-visible');
        // Restore booking modal
        if (bookingModal) bookingModal.style.display = '';
        if (bookingModalContent) bookingModalContent.style.display = '';
        if (paymentErrorEl) {
            paymentErrorEl.style.display = 'none';
            paymentErrorEl.textContent = '';
        }
        // Clean up payment element
        if (paymentElement) {
            try {
                paymentElement.unmount();
            } catch (e) {
                console.warn('Error unmounting payment element:', e);
            }
            paymentElement = null;
        }
        if (elements) {
            elements = null;
        }
        currentPaymentIntent = null;
    }

    function initializeStripe(publishableKey) {
        if (!window.Stripe) {
            console.error('Stripe.js not loaded');
            return false;
        }
        
        stripe = window.Stripe(publishableKey);
        return true;
    }

    function initializePaymentFlow(client_secret, publishable_key, amount, currency) {
        // Initialize Stripe
        if (!initializeStripe(publishable_key)) {
            throw new Error('<?php echo esc_js(__('Failed to initialize payment system.', 'nymia')); ?>');
        }
        
        // Create payment element
        if (!createPaymentElement(client_secret)) {
            throw new Error('<?php echo esc_js(__('Failed to create payment form.', 'nymia')); ?>');
        }
        
        currentPaymentIntent = client_secret;
        
        // Show payment modal
        const itemTitle = state.room.stream_title || '';
        showPaymentModal(amount, currency, itemTitle);
    }

    function createPaymentElement(clientSecret) {
        if (!stripe || !paymentElementContainer) {
            return false;
        }

        // Clean up existing element if any
        if (paymentElement) {
            try {
                paymentElement.unmount();
            } catch (e) {
                console.warn('Error unmounting existing payment element:', e);
            }
            paymentElement = null;
        }

        // Clear container
        paymentElementContainer.innerHTML = '';

        elements = stripe.elements({
            clientSecret: clientSecret,
            appearance: {
                theme: 'night',
                variables: {
                    colorPrimary: '#667eea',
                    colorBackground: '#1a1a1a',
                    colorText: '#ffffff',
                    colorDanger: '#ef4444',
                    fontFamily: 'system-ui, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '8px',
                },
            },
        });

        paymentElement = elements.create('payment');
        paymentElement.mount(paymentElementContainer);
        
        paymentElement.on('ready', () => {
            if (paymentSubmitBtn) paymentSubmitBtn.disabled = false;
        });

        paymentElement.on('change', (event) => {
            if (event.error && paymentErrorEl) {
                paymentErrorEl.textContent = event.error.message;
                paymentErrorEl.style.display = 'block';
            } else if (paymentErrorEl) {
                paymentErrorEl.style.display = 'none';
                paymentErrorEl.textContent = '';
            }
        });

        return true;
    }

    function handleFormSubmit(event) {
        event.preventDefault();

        if (!isLoggedIn) {
            // Show login modal popup instead of redirecting
            if (typeof window.openLoginModal === 'function') {
                window.openLoginModal('login');
            } else {
                // Fallback to redirect if modal function is not available
            window.location.href = loginUrl;
            }
            return;
        }

        if (!bookingForm || !submitBtn) {
            return;
        }

        const bookingType = state.room.booking_type || 'live';
        
        // Only handle live stream bookings with embedded payment
        if (bookingType !== 'live') {
            // For private sessions, use old redirect flow
            const formData = new FormData(bookingForm);
            formData.append('action', 'nymia_book_private_session');
            formData.append('nonce', bookingNonce);
            formData.append('room_id', state.room.room_id || '');
            formData.append('slot_id', state.room.slot_id || '');
            formData.append('creator_id', state.room.creator_id || '');
            formData.append('stream_title', state.room.stream_title || '');
            
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            submitBtn.textContent = '<?php echo esc_js(__('Processing…', 'nymia')); ?>';

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success && data.data && data.data.checkout_url) {
                        window.location.href = data.data.checkout_url;
                        return;
                    }
                    throw new Error(data?.data?.message || data?.message || '<?php echo esc_js(__('Booking failed. Please try again.', 'nymia')); ?>');
                })
                .catch(error => {
                    alert(error.message);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            return;
        }

        // For live streams, create payment intent and show payment form
        const formData = new FormData(bookingForm);
        formData.append('action', 'nymia_create_live_stream_payment_intent');
        formData.append('nonce', bookingNonce);
        formData.append('room_id', state.room.room_id || '');
        formData.append('creator_id', state.room.creator_id || '');
        formData.append('stream_title', state.room.stream_title || '');
        formData.append('is_scheduled', state.room.is_scheduled ? '1' : '0');
        formData.append('start_timestamp', state.room.start_timestamp || '0');
        if (reminderSelect && !reminderSelect.disabled) {
            formData.append('reminder_minutes', reminderSelect.value || '');
        } else {
            formData.append('reminder_minutes', '');
        }

        const selectedType = bookingForm.querySelector('input[name="payment_type"]:checked')?.value || 'full';
        formData.set('payment_type', selectedType);
        formData.set('minutes', minutesInput ? (minutesInput.value || '0') : '0');

        submitBtn.disabled = true;
        const originalText = submitBtn.textContent;
        submitBtn.textContent = '<?php echo esc_js(__('Loading…', 'nymia')); ?>';

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                // Always get text first to see what we're dealing with
                return response.text().then(text => {
                    // Check if response is actually JSON
                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        console.error('Non-JSON response received:', text.substring(0, 200));
                        // Try to extract error message from HTML if possible
                        const errorMatch = text.match(/<p[^>]*>([^<]+)<\/p>/i);
                        const errorMsg = errorMatch ? errorMatch[1] : '<?php echo esc_js(__('Server returned an invalid response. Please try again.', 'nymia')); ?>';
                        throw new Error(errorMsg);
                    }
                    
                    // Try to parse as JSON
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response text:', text.substring(0, 500));
                        throw new Error('<?php echo esc_js(__('Invalid response from server. Please try again.', 'nymia')); ?>');
                    }
                });
            })
            .then(data => {
                if (data && data.success && data.data) {
                    const { client_secret, publishable_key, amount, currency } = data.data;
                    
                    // Wait for Stripe.js to load if not already loaded
                    if (!window.Stripe) {
                        // Stripe.js should be loaded via script tag, wait a bit
                        setTimeout(() => {
                            if (!window.Stripe) {
                                alert('<?php echo esc_js(__('Payment system not loaded. Please refresh the page.', 'nymia')); ?>');
                                submitBtn.disabled = false;
                                submitBtn.textContent = originalText;
                                return;
                            }
                            try {
                                initializePaymentFlow(client_secret, publishable_key, amount, currency);
                            } catch (err) {
                                alert(err.message || '<?php echo esc_js(__('Failed to initialize payment.', 'nymia')); ?>');
                                submitBtn.disabled = false;
                                submitBtn.textContent = originalText;
                            }
                        }, 500);
                    } else {
                        try {
                            initializePaymentFlow(client_secret, publishable_key, amount, currency);
                        } catch (err) {
                            alert(err.message || '<?php echo esc_js(__('Failed to initialize payment.', 'nymia')); ?>');
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }
                    }
                } else {
                    throw new Error(data?.data?.message || data?.message || '<?php echo esc_js(__('Failed to initialize payment. Please try again.', 'nymia')); ?>');
                }
            })
            .catch(error => {
                alert(error.message);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
    }

    function handlePaymentSubmit(event) {
        event.preventDefault();
        
        if (!stripe || !paymentElement || !currentPaymentIntent) {
            return;
        }

        if (paymentSubmitBtn) {
            paymentSubmitBtn.disabled = true;
            paymentSubmitBtn.textContent = '<?php echo esc_js(__('Processing…', 'nymia')); ?>';
        }

        // Extract payment intent ID from client secret
        const paymentIntentId = currentPaymentIntent.split('_secret_')[0];

        stripe.confirmPayment({
            elements: elements,
            confirmParams: {
                return_url: window.location.href,
            },
            redirect: 'if_required',
        })
        .then((result) => {
            if (result.error) {
                if (paymentErrorEl) {
                    paymentErrorEl.textContent = result.error.message;
                    paymentErrorEl.style.display = 'block';
                }
                if (paymentSubmitBtn) {
                    paymentSubmitBtn.disabled = false;
                    paymentSubmitBtn.textContent = '<?php echo esc_js(__('Pay Now', 'nymia')); ?>';
                }
            } else {
                // Payment succeeded, confirm on server
                const formData = new FormData();
                formData.append('action', 'nymia_confirm_live_stream_payment');
                formData.append('nonce', bookingNonce);
                formData.append('payment_intent_id', paymentIntentId);

                return fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        const responseData = data.data || {};
                        const isScheduled = responseData.is_scheduled === true || responseData.is_scheduled === '1';
                        const startTimestamp = parseInt(responseData.start_timestamp || 0);
                        const streamTitle = responseData.stream_title || '';
                        
                        // Close payment modal
                        hidePaymentModal();
                        
                        if (isScheduled && startTimestamp > 0) {
                            // Show success message for scheduled bookings
                            showScheduledBookingSuccess(streamTitle, startTimestamp, responseData.room_id);
                        } else {
                            // For immediate/live bookings, redirect to live stream
                            closeModal();
                            if (responseData.redirect_url) {
                                window.location.href = responseData.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        }
                    } else {
                        throw new Error(data?.data?.message || '<?php echo esc_js(__('Payment confirmation failed.', 'nymia')); ?>');
                    }
                });
            }
        })
        .catch(error => {
            console.error('Payment error:', error);
            if (paymentErrorEl) {
                paymentErrorEl.textContent = error.message || '<?php echo esc_js(__('Payment failed. Please try again.', 'nymia')); ?>';
                paymentErrorEl.style.display = 'block';
            }
            if (paymentSubmitBtn) {
                paymentSubmitBtn.disabled = false;
                paymentSubmitBtn.textContent = '<?php echo esc_js(__('Pay Now', 'nymia')); ?>';
            }
        });
    }

    // Event bindings
    if (bookingCloseBtn) {
        bookingCloseBtn.addEventListener('click', closeModal);
    }
    if (bookingOverlay) {
        bookingOverlay.addEventListener('click', function() {
            if (paymentModal && paymentModal.classList.contains('is-visible')) {
                hidePaymentModal();
            } else {
                closeModal();
            }
        });
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e){
            e.preventDefault();
            closeModal();
        });
    }
    if (paymentCloseBtn) {
        paymentCloseBtn.addEventListener('click', hidePaymentModal);
    }
    if (paymentCancelBtn) {
        paymentCancelBtn.addEventListener('click', hidePaymentModal);
    }
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleFormSubmit);
    }
    if (paymentForm) {
        paymentForm.addEventListener('submit', handlePaymentSubmit);
    }
    if (minutesInput) {
        minutesInput.addEventListener('input', updateTotalPrice);
    }
    paymentTypeRadios.forEach(radio => {
        radio.addEventListener('change', function(){
            updateMinutesState();
            updateTotalPrice();
        });
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') {
            if (paymentModal && paymentModal.classList.contains('is-visible')) {
                hidePaymentModal();
            } else {
                closeModal();
            }
        }
    });

    // Expose global helpers
    window.nymiaOpenBookingModal = openModal;
    window.nymiaCloseBookingModal = closeModal;
})();
</script>

