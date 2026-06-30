


<?php $__env->startSection('title', 'Checkout - KidsStore'); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/checkout.css')); ?>">
    <style>
        /* Payment Method Styles */
        .payment-method-option {
            position: relative;
            cursor: pointer;
        }

        .payment-method-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .payment-method-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            width: 100%;
        }

        .payment-method-label i {
            font-size: 2rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .payment-method-label span {
            font-weight: 600;
            color: #212529;
        }

        .payment-method-label small {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .payment-method-option input[type="radio"]:checked + .payment-method-label {
            border-color: #0d9488;
            background: #f0fdfa;
            box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.2);
        }

        .payment-method-option input[type="radio"]:checked + .payment-method-label i {
            color: #0d9488;
        }

        .payment-method-option:hover .payment-method-label {
            border-color: #0d9488;
        }

        /* ============================================ */
        /* PAYMENT PROGRESS OVERLAY */
        /* ============================================ */
        #paymentOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            z-index: 99999;
            justify-content: center;
            align-items: center;
        }

        #paymentOverlay.active {
            display: flex;
        }

        .payment-box {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 420px;
            width: 90%;
            text-align: center;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .payment-spinner {
            width: 64px;
            height: 64px;
            border: 4px solid #e9ecef;
            border-top-color: #0d9488;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1.5rem;
        }

        .payment-spinner.success {
            border-color: #10b981;
            border-top-color: #10b981;
        }

        .payment-spinner.failed {
            border-color: #ef4444;
            border-top-color: #ef4444;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .payment-title {
            font-weight: 600;
            color: #0d9488;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .payment-title.success {
            color: #10b981;
        }

        .payment-title.failed {
            color: #ef4444;
        }

        .payment-message {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .progress-bar-wrapper {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #0d9488, #14b8a6);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .progress-bar-fill.success {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .progress-bar-fill.failed {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }

        .payment-percentage {
            color: #6c757d;
            font-size: 0.85rem;
            margin: 0;
        }

        .payment-cancel-btn {
            margin-top: 1.5rem;
            padding: 0.5rem 2rem;
            border: 1px solid #e9ecef;
            background: transparent;
            border-radius: 8px;
            color: #6c757d;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .payment-cancel-btn:hover {
            background: #f8f9fa;
        }

        .payment-retry-btn {
            border-color: #0d9488 !important;
            color: #0d9488 !important;
            margin-right: 8px;
        }

        .payment-retry-btn:hover {
            background: #f0fdfa !important;
        }

        /* ============================================ */
        /* RESPONSIVE */
        /* ============================================ */
        @media (max-width: 768px) {
            .payment-box {
                padding: 1.5rem;
            }
            .payment-spinner {
                width: 48px;
                height: 48px;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
        $headerLogo = isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png');
    ?>

    <main class="shop-container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2 mb-0 d-flex align-items-center gap-2" style="color: var(--teal-primary, #0d9488);">
                        <img src="<?php echo e($headerLogo); ?>" alt="KidsStore Logo"
                            style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px;">
                        <span>Order Details</span>
                    </h1>
                    <a href="<?php echo e(route('cart.index')); ?>" class="btn btn-outline-secondary checkout-dot-btn checkout-desktop-action" data-spin-link="1">
                        <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                        <span class="button-text"><i class="bi bi-arrow-left"></i> Back to Cart</span>
                    </a>
                </div>

                <form id="checkoutForm" action="<?php echo e(route('checkout.store')); ?>" method="POST" class="checkout-form">
                    <?php echo csrf_field(); ?>

                    <div class="row g-4">
                        <!-- Left Column - Customer Information Forms -->
                        <div class="col-lg-8">
                            <!-- Required Information Form -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Required Information</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Personal Information Section -->
                                    <div class="mb-4">
                                        <h6 class="section-title mb-3">Personal Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">First Name *</label>
                                                <input type="text" name="first_name" class="form-control form-control-lg"
                                                    value="<?php echo e(old('first_name', $savedCheckoutInfo->first_name ?? '')); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Last Name *</label>
                                                <input type="text" name="last_name" class="form-control form-control-lg"
                                                    value="<?php echo e(old('last_name', $savedCheckoutInfo->last_name ?? '')); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email Address *</label>
                                                <input type="email" name="email" class="form-control form-control-lg"
                                                    value="<?php echo e($user->email ?? old('email')); ?>" required readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone Number *</label>
                                                <input type="text" name="phone_number"
                                                    class="form-control form-control-lg" placeholder="+255XXXXXXXXX"
                                                    value="<?php echo e($checkoutPhoneNumber ?: old('phone_number')); ?>"
                                                    maxlength="13" pattern="^\+255[0-9]{9}$" required readonly>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        id="saveRequiredInformation" name="save_required_information"
                                                        <?php echo e(old('save_required_information', 1) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="saveRequiredInformation">
                                                        Save Required Information for next checkout
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ✅ PAYMENT METHOD SECTION -->
                                    <div class="mb-4">
                                        <h6 class="section-title mb-3">Payment Method</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="payment-method-option">
                                                    <input type="radio" name="payment_method" id="payment_mpesa" value="mpesa" checked>
                                                    <label for="payment_mpesa" class="payment-method-label">
                                                        <i class="bi bi-phone"></i>
                                                        <span>M-Pesa</span>
                                                        <small>Pay using mobile money</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="payment-method-option">
                                                    <input type="radio" name="payment_method" id="payment_bank" value="bank">
                                                    <label for="payment_bank" class="payment-method-label">
                                                        <i class="bi bi-bank"></i>
                                                        <span>Bank Transfer</span>
                                                        <small>Pay via mobile banking</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="payment-method-option">
                                                    <input type="radio" name="payment_method" id="payment_card" value="card">
                                                    <label for="payment_card" class="payment-method-label">
                                                        <i class="bi bi-credit-card"></i>
                                                        <span>Card Payment</span>
                                                        <small>Visa / Mastercard</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column - Order Summary -->
                        <div class="col-lg-4">
                            <div class="card order-summary-card">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Products -->
                                    <div class="mb-4" id="orderSummaryProducts">
                                        <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($loop->index < 3): ?>
                                                <div class="product-item">
                                                    <div class="product-item-image-wrap">
                                                        <img src="<?php echo e($item->product->thumbnail
                                                            ? asset('storage/' . $item->product->thumbnail)
                                                            : ($item->product->media->where('is_primary', true)->first()
                                                                ? asset('storage/' . $item->product->media->where('is_primary', true)->first()->file_path)
                                                                : asset('img/logo.png'))); ?>"
                                                            alt="<?php echo e($item->product->name); ?>">
                                                    </div>
                                                    <div class="product-item-content">
                                                        <h6 class="product-item-title mb-1 text-truncate"><?php echo e($item->product->name); ?></h6>
                                                        <p class="product-item-qty mb-0">Qty: <?php echo e($item->quantity); ?></p>
                                                    </div>
                                                    <div class="product-item-total text-end">
                                                        <strong class="text-primary"><?php echo e(format_money_short($item->price * $item->quantity, 2)); ?></strong>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        <?php if($cartItems->count() > 3): ?>
                                            <div id="orderSummaryExtra" class="order-summary-extra" style="display: none;">
                                                <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($loop->index >= 3): ?>
                                                        <div class="product-item">
                                                            <div class="product-item-image-wrap">
                                                                <img src="<?php echo e($item->product->thumbnail
                                                                    ? asset('storage/' . $item->product->thumbnail)
                                                                    : ($item->product->media->where('is_primary', true)->first()
                                                                        ? asset('storage/' . $item->product->media->where('is_primary', true)->first()->file_path)
                                                                        : asset('img/logo.png'))); ?>"
                                                                    alt="<?php echo e($item->product->name); ?>">
                                                            </div>
                                                            <div class="product-item-content">
                                                                <h6 class="product-item-title mb-1 text-truncate"><?php echo e($item->product->name); ?></h6>
                                                                <p class="product-item-qty mb-0">Qty: <?php echo e($item->quantity); ?></p>
                                                            </div>
                                                            <div class="product-item-total text-end">
                                                                <strong class="text-primary"><?php echo e(format_money_short($item->price * $item->quantity, 2)); ?></strong>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>

                                            <button type="button" class="btn btn-link p-0 mt-2 summary-toggle-btn" id="orderSummaryToggleBtn"
                                                data-expanded="false" data-hidden-count="<?php echo e($cartItems->count() - 3); ?>">
                                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                                <span>Show more (<?php echo e($cartItems->count() - 3); ?>)</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Order Totals -->
                                    <div class="order-total">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal (<?php echo e($cartItems->sum('quantity')); ?> items)</span>
                                            <span><?php echo e(format_money_short($subtotal, 2)); ?></span>
                                        </div>
                                        <hr class="my-3">
                                        <div class="d-flex justify-content-between">
                                            <strong class="fs-5">Total</strong>
                                            <strong class="total-amount fs-5"><?php echo e(format_money_short($total, 2)); ?></strong>
                                        </div>
                                    </div>

                                    <!-- Place Order Button -->
                                    <button type="button" onclick="confirmOrder()" class="btn btn-primary checkout-primary-btn w-100 mt-4 checkout-dot-btn checkout-desktop-action" id="placeOrderBtn">
                                        <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                        <span class="button-text"><i class="bi bi-credit-card me-2"></i>Place Order</span>
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="checkout-mobile-actions" aria-label="Mobile checkout actions">
            <a href="<?php echo e(route('cart.index')); ?>" class="btn btn-outline-secondary checkout-dot-btn" data-spin-link="1">
                <span class="btn-dot-spinner" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="button-text"><i class="bi bi-arrow-left me-1"></i> Back to Cart</span>
            </a>
            <button type="button" onclick="confirmOrder()" class="btn btn-primary checkout-primary-btn checkout-dot-btn" id="placeOrderBtnMobile">
                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="button-text"><i class="bi bi-credit-card me-1"></i>Place Order</span>
            </button>
        </div>

        <!-- ============================================ -->
        <!-- PAYMENT PROCESSING OVERLAY WITH PROGRESS BAR -->
        <!-- ============================================ -->
        <div id="paymentOverlay">
            <div class="payment-box">
                <!-- Spinner -->
                <div class="payment-spinner" id="paymentSpinner"></div>
                
                <!-- Title -->
                <h5 class="payment-title" id="paymentTitle">Processing Your Payment</h5>
                <p class="payment-message" id="paymentMessage">Please wait, do not refresh the page...</p>
                
                <!-- Progress Bar -->
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" id="paymentProgressBar"></div>
                </div>
                
                <!-- Percentage -->
                <p class="payment-percentage" id="paymentPercentage">0%</p>
                
                <!-- Buttons Container -->
                <div id="paymentButtonsContainer">
                    <button class="payment-cancel-btn" onclick="cancelPayment()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        // ============================================
        // OVERRIDE EXISTING CHECKOUT.JS FUNCTIONS
        // ============================================
        console.log('Checkout page loaded - OVERRIDE MODE');
        
        // ============================================
        // PAYMENT PROGRESS BAR CONTROLS
        // ============================================
        let paymentInterval = null;
        let paymentProgress = 0;
        let isPaymentComplete = false;
        let paymentAbortController = null;

        function showPaymentOverlay() {
            console.log('showPaymentOverlay called');
            const overlay = document.getElementById('paymentOverlay');
            if (!overlay) {
                console.error('paymentOverlay not found!');
                return;
            }
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            paymentProgress = 0;
            isPaymentComplete = false;
            
            // Reset spinner
            const spinner = document.getElementById('paymentSpinner');
            if (spinner) spinner.className = 'payment-spinner';
            
            // Reset title
            const title = document.getElementById('paymentTitle');
            if (title) {
                title.className = 'payment-title';
                title.textContent = 'Processing Your Payment';
            }
            
            // Reset message
            const message = document.getElementById('paymentMessage');
            if (message) message.textContent = 'Please wait, do not refresh the page...';
            
            // Reset progress bar
            const progressBar = document.getElementById('paymentProgressBar');
            if (progressBar) {
                progressBar.className = 'progress-bar-fill';
                progressBar.style.width = '0%';
            }
            
            // Reset percentage
            const percentage = document.getElementById('paymentPercentage');
            if (percentage) percentage.textContent = '0%';
            
            // Remove any existing retry button
            const retryBtn = document.querySelector('.payment-retry-btn');
            if (retryBtn) retryBtn.remove();
            
            // Show cancel button
            const cancelBtn = document.querySelector('.payment-cancel-btn');
            if (cancelBtn) cancelBtn.style.display = 'inline-block';
            
            startProgressAnimation();
        }

        function hidePaymentOverlay() {
            console.log('hidePaymentOverlay called');
            const overlay = document.getElementById('paymentOverlay');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
            clearInterval(paymentInterval);
            
            // Remove retry button if exists
            const retryBtn = document.querySelector('.payment-retry-btn');
            if (retryBtn) retryBtn.remove();
        }

        function updateProgress(percentage) {
            const progressBar = document.getElementById('paymentProgressBar');
            const percentageText = document.getElementById('paymentPercentage');
            
            if (progressBar) {
                progressBar.style.width = percentage + '%';
            }
            if (percentageText) {
                percentageText.textContent = percentage + '%';
            }
            
            const title = document.getElementById('paymentTitle');
            const message = document.getElementById('paymentMessage');
            
            if (percentage < 30) {
                if (title) title.textContent = '⏳ Initiating Payment...';
                if (message) message.textContent = 'Connecting to payment gateway...';
            } else if (percentage < 60) {
                if (title) title.textContent = '⏳ Processing Payment...';
                if (message) message.textContent = 'Verifying your payment details...';
            } else if (percentage < 90) {
                if (title) title.textContent = '⏳ Finalizing Payment...';
                if (message) message.textContent = 'Please wait, almost done...';
            } else {
                if (title) title.textContent = '⏳ Completing Order...';
                if (message) message.textContent = 'Confirming your purchase...';
            }
        }

        function startProgressAnimation() {
            clearInterval(paymentInterval);
            
            paymentInterval = setInterval(function() {
                if (isPaymentComplete) {
                    clearInterval(paymentInterval);
                    return;
                }
                
                const increment = Math.floor(Math.random() * 5) + 1;
                paymentProgress = Math.min(paymentProgress + increment, 95);
                updateProgress(paymentProgress);
            }, 300);
        }

        function completePayment(success = true) {
            isPaymentComplete = true;
            clearInterval(paymentInterval);
            updateProgress(100);
            
            const spinner = document.getElementById('paymentSpinner');
            const title = document.getElementById('paymentTitle');
            const message = document.getElementById('paymentMessage');
            const progressBar = document.getElementById('paymentProgressBar');
            
            if (success) {
                if (spinner) spinner.className = 'payment-spinner success';
                if (title) {
                    title.className = 'payment-title success';
                    title.textContent = 'Payment Successful!';
                }
                if (message) message.textContent = 'Your order has been confirmed. Redirecting...';
                if (progressBar) progressBar.className = 'progress-bar-fill success';
            } else {
                if (spinner) spinner.className = 'payment-spinner failed';
                if (title) {
                    title.className = 'payment-title failed';
                    title.textContent = 'Payment Failed';
                }
                if (message) message.textContent = 'Payment failed. Please try again or use a different payment method.';
                if (progressBar) progressBar.className = 'progress-bar-fill failed';
            }
        }

        function cancelPayment() {
            if (confirm('Are you sure you want to cancel this payment?')) {
                isPaymentComplete = true;
                clearInterval(paymentInterval);
                
                // Abort the fetch request if possible
                if (paymentAbortController) {
                    paymentAbortController.abort();
                }
                
                hidePaymentOverlay();
                window.location.href = '<?php echo e(route('cart.index')); ?>';
            }
        }

        function showRetryButton() {
            // Remove existing retry button if any
            const existingRetry = document.querySelector('.payment-retry-btn');
            if (existingRetry) existingRetry.remove();
            
            const container = document.getElementById('paymentButtonsContainer');
            if (!container) return;
            
            const retryBtn = document.createElement('button');
            retryBtn.className = 'payment-cancel-btn payment-retry-btn';
            retryBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Retry Payment';
            retryBtn.onclick = function() {
                hidePaymentOverlay();
                // Wait a moment then retry
                setTimeout(() => confirmOrder(), 500);
            };
            
            // Insert retry button before cancel button
            const cancelBtn = container.querySelector('.payment-cancel-btn');
            if (cancelBtn) {
                container.insertBefore(retryBtn, cancelBtn);
                cancelBtn.textContent = 'Cancel';
                cancelBtn.style.display = 'inline-block';
            } else {
                container.appendChild(retryBtn);
            }
        }

        // ============================================
        // ✅ MAIN CONFIRM ORDER FUNCTION - UPDATED
        // ============================================
        function confirmOrder() {
            console.log('confirmOrder called - UPDATED VERSION');
            
            const form = document.getElementById('checkoutForm');
            if (!form) {
                console.error('Form not found!');
                alert('Form not found. Please refresh and try again.');
                return;
            }
            
            // Get form data
            const formData = new FormData(form);
            
            // Validate required fields
            const firstName = formData.get('first_name');
            const lastName = formData.get('last_name');
            const email = formData.get('email');
            const phone = formData.get('phone_number');
            
            if (!firstName || !lastName || !email || !phone) {
                alert('Please fill in all required fields.');
                return;
            }
            
            console.log('Form validation passed');
            
            // Disable buttons
            const placeOrderBtn = document.getElementById('placeOrderBtn');
            const placeOrderBtnMobile = document.getElementById('placeOrderBtnMobile');
            if (placeOrderBtn) placeOrderBtn.disabled = true;
            if (placeOrderBtnMobile) placeOrderBtnMobile.disabled = true;
            
            // Show payment overlay
            showPaymentOverlay();
            
            // Get CSRF token
            const token = document.querySelector('input[name="_token"]')?.value || '';
            
            // Create abort controller for timeout
            paymentAbortController = new AbortController();
            const timeoutId = setTimeout(() => {
                if (paymentAbortController) {
                    paymentAbortController.abort();
                }
            }, 30000); // 30 second timeout
            
            // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                signal: paymentAbortController.signal
            })
            .then(response => {
                clearTimeout(timeoutId);
                console.log('Response status:', response.status);
                
                // Get response body as text first for debugging
                return response.text().then(text => {
                    try {
                        return { 
                            ok: response.ok, 
                            status: response.status, 
                            data: JSON.parse(text) 
                        };
                    } catch (e) {
                        console.error('Failed to parse JSON:', text);
                        throw new Error('Server returned invalid response');
                    }
                });
            })
            .then(({ ok, status, data }) => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // ✅ Payment successful
                    completePayment(true);
                    setTimeout(() => {
                        hidePaymentOverlay();
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = '<?php echo e(route('customer.orders')); ?>';
                        }
                    }, 2000);
                } else {
                    // ❌ Payment failed - show error but keep cart
                    completePayment(false);
                    const msgEl = document.getElementById('paymentMessage');
                    if (msgEl) {
                        msgEl.textContent = data.message || 'Payment failed. Please try again.';
                    }
                    
                    // Show retry button
                    showRetryButton();
                    
                    // Enable buttons so user can retry
                    if (placeOrderBtn) placeOrderBtn.disabled = false;
                    if (placeOrderBtnMobile) placeOrderBtnMobile.disabled = false;
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Error:', error);
                
                completePayment(false);
                const msgEl = document.getElementById('paymentMessage');
                if (msgEl) {
                    if (error.name === 'AbortError') {
                        msgEl.textContent = 'Payment request timed out. Please try again.';
                    } else {
                        msgEl.textContent = 'An error occurred: ' + error.message;
                    }
                }
                
                // Show retry button
                showRetryButton();
                
                // Enable buttons
                if (placeOrderBtn) placeOrderBtn.disabled = false;
                if (placeOrderBtnMobile) placeOrderBtnMobile.disabled = false;
            });
        }

        // ============================================
        // TOGGLE ORDER SUMMARY
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - OVERRIDE MODE');
            console.log('confirmOrder function type:', typeof confirmOrder);
            
            const toggleBtn = document.getElementById('orderSummaryToggleBtn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const extra = document.getElementById('orderSummaryExtra');
                    const isExpanded = this.dataset.expanded === 'true';
                    const hiddenCount = this.dataset.hiddenCount || 0;
                    
                    if (isExpanded) {
                        extra.style.display = 'none';
                        this.dataset.expanded = 'false';
                        this.innerHTML = `<i class="bi bi-chevron-down" aria-hidden="true"></i><span>Show more (${hiddenCount})</span>`;
                    } else {
                        extra.style.display = 'block';
                        this.dataset.expanded = 'true';
                        this.innerHTML = `<i class="bi bi-chevron-up" aria-hidden="true"></i><span>Show less</span>`;
                    }
                });
            }
        });
    </script>

<?php $__env->startSection('scripts'); ?>
    
    
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\recommendation_system_logic\resources\views/shop/checkout.blade.php ENDPATH**/ ?>