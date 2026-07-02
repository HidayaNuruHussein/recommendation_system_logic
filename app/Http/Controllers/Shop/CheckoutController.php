<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CheckoutInformation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page
     */
    public function index()
    {
        if (!Auth::check()) {
            session(['intended' => route('checkout.index')]);
            return redirect()->route('register')
                ->with('info', 'Please register or login to proceed with checkout');
        }

        $user = Auth::user();
        $savedCheckoutInfo = $user->checkoutInformation()->first();
        $checkoutPhoneNumber = $this->formatTzPhoneForDisplay($user->phone_number);
        $cart = $this->getCart();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty');
        }

        $cartItems = $cart->cartItems()->with('product.media')->get();
        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $total = $subtotal;

        return view('shop.checkout', compact(
            'cart',
            'cartItems',
            'subtotal',
            'total',
            'user',
            'checkoutPhoneNumber',
            'savedCheckoutInfo'
        ));
    }

    /**
     * Process the checkout with payment simulation
     */
    public function store(Request $request)
    {
        // Enable detailed error logging
        Log::info('Checkout store started', [
            'user_id' => Auth::id(),
        ]);

        try {
            // Normalize phone number
            $rawPhone = (string) $request->input('phone_number', '');
            $digitsOnly = $this->normalizeTzPhoneToLocal9($rawPhone);
            $request->merge(['phone_number' => $digitsOnly]);

            // ✅ FIXED: Using 'digits' instead of regex to avoid delimiter issues
            $rules = [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'required|string|digits:9', // ✅ No regex needed
                'save_required_information' => 'nullable|boolean',
                'payment_method' => 'required|string|in:mpesa,airtel_money,mixx_by_yas,halopesa,crdb,nmb,absa,tpb,visa,mastercard,american_express',
            ];

            // ✅ Add card validation if card payment is selected
            $cardMethods = ['visa', 'mastercard', 'american_express'];
            if (in_array($request->payment_method, $cardMethods)) {
                $rules['card_number'] = 'required|string|min:16|max:19';
                $rules['card_expiry'] = 'required|string|size:5'; // ✅ Simple size validation
                $rules['card_cvv'] = 'required|string|min:3|max:4';
                $rules['cardholder_name'] = 'required|string|min:2|max:255';
            }

            try {
                $request->validate($rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please fill in all required fields correctly.',
                    'errors' => $e->errors()
                ], 422);
            }

            $user = Auth::user();
            $cart = $this->getCart();

            if (!$cart || $cart->cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Please add items to your cart before checkout.'
                ], 400);
            }

            $cartItems = $cart->cartItems()->with('product')->get();

            // Check stock availability
            foreach ($cartItems as $item) {
                if (!$item->product) {
                    return response()->json([
                        'success' => false,
                        'message' => "Product not found for one of your cart items."
                    ], 400);
                }
                
                if ($item->quantity > $item->product->stock) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$item->product->name}. Available stock: {$item->product->stock}"
                    ], 400);
                }
            }

            // Calculate totals
            $subtotal = $cartItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $totalAmount = $subtotal;
            $formattedPhone = '+255' . $digitsOnly;

            // ✅ START TRANSACTION
            DB::beginTransaction();
            try {
                // ✅ 1. CREATE ORDER (status: pending)
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'total_amount' => $totalAmount,
                    'currency' => 'TZS',
                    'ordered_at' => now(),
                    'customer_name' => $request->first_name . ' ' . $request->last_name,
                    'customer_email' => $request->email,
                    'customer_phone' => $formattedPhone,
                    'stock_deducted_at' => null,
                ]);

                // ✅ 2. CREATE ORDER ITEMS (NO STOCK DEDUCTION YET)
                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name,
                        'product_sku' => $cartItem->product->sku ?? null,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->price,
                        'total_price' => $cartItem->price * $cartItem->quantity,
                    ]);
                }

                // ✅ 3. SAVE CHECKOUT INFORMATION
                if ($request->boolean('save_required_information')) {
                    CheckoutInformation::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'first_name' => $request->first_name,
                            'last_name' => $request->last_name,
                            'email' => $request->email,
                            'phone_number' => $formattedPhone,
                        ]
                    );
                }

                // ✅ 4. PROCESS PAYMENT SIMULATION
                $paymentMethod = $request->input('payment_method', 'mpesa');
                
                // ✅ Include card details if available
                $cardDetails = null;
                if (in_array($paymentMethod, ['visa', 'mastercard', 'american_express'])) {
                    $cardDetails = [
                        'card_number' => $this->maskCardNumber($request->input('card_number', '')),
                        'card_expiry' => $request->input('card_expiry', ''),
                        'cardholder_name' => $request->input('cardholder_name', ''),
                        'card_type' => $paymentMethod,
                    ];
                }
                
                $paymentResult = $this->simulatePayment($order, $request, $paymentMethod, $cardDetails);

                // ✅ 5. HANDLE PAYMENT RESULT
                if ($paymentResult['success']) {
                    // ✅ Payment successful: Deduct stock, clear cart, update order
                    $this->deductStock($order);
                    
                    // Clear cart
                    $cart->cartItems()->delete();
                    $cart->delete();
                    
                    $order->update([
                        'status' => 'completed',
                        'stock_deducted_at' => now(),
                    ]);
                    
                    DB::commit();
                    
                    Log::info('Payment successful', ['order_id' => $order->id]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment successful! Your order has been confirmed.',
                        'order_id' => $order->id,
                        'payment' => [
                            'transaction_id' => $paymentResult['transaction_id'],
                            'amount' => $paymentResult['amount'],
                            'status' => 'completed',
                            'payment_method' => $paymentMethod,
                        ],
                        'redirect_url' => route('shop')
                    ]);
                } else {
                    // ✅ Payment failed
                    $order->update([
                        'status' => 'cancelled',
                    ]);
                    
                    DB::commit();
                    
                    Log::warning('Payment failed', ['order_id' => $order->id]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => $paymentResult['message'] ?? 'Payment failed. Please try again.',
                        'order_id' => $order->id,
                        'payment' => [
                            'transaction_id' => $paymentResult['transaction_id'],
                            'amount' => $paymentResult['amount'],
                            'status' => 'failed',
                            'payment_method' => $paymentMethod,
                        ],
                        'cart_restored' => true,
                        'redirect_url' => route('cart.index')
                    ], 400);
                }

            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Transaction failed', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to place order. Error: ' . $e->getMessage(),
                    'redirect_url' => route('cart.index')
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Checkout failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to place order. Error: ' . $e->getMessage(),
                'redirect_url' => route('cart.index')
            ], 500);
        }
    }

    /**
     * ✅ Mask card number for security
     */
    private function maskCardNumber($cardNumber)
    {
        // Remove spaces and non-digits
        $clean = preg_replace('/\D/', '', $cardNumber);
        $length = strlen($clean);
        
        if ($length <= 4) {
            return $clean;
        }
        
        // Show only last 4 digits
        $last4 = substr($clean, -4);
        $masked = str_repeat('*', $length - 4) . $last4;
        
        // Format with spaces every 4 digits
        return implode(' ', str_split($masked, 4));
    }

    /**
     * ✅ PAYMENT SUCCESS PAGE
     */
    public function success($orderId)
    {
        Log::info('Payment success page', ['order_id' => $orderId]);
        
        // Try to find order by ID first, then by public_id
        $order = Order::where('id', $orderId)
            ->orWhere('public_id', $orderId)
            ->first();
        
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['orderItems.product', 'payments']);
        $payment = $order->payments()->latest()->first();

        return view('shop.checkout-success', compact('order', 'payment'));
    }

    /**
     * ✅ PAYMENT FAILED PAGE
     */
    public function failed($orderId)
    {
        Log::info('Payment failed page', ['order_id' => $orderId]);
        
        // Try to find order by ID first, then by public_id
        $order = Order::where('id', $orderId)
            ->orWhere('public_id', $orderId)
            ->first();
        
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['orderItems.product', 'payments']);
        $payment = $order->payments()->latest()->first();

        return view('shop.checkout-failed', compact('order', 'payment'));
    }

    /**
     * ✅ RETRY PAYMENT
     */
    public function retry(Request $request, $orderId)
    {
        Log::info('Retry payment', ['order_id' => $orderId]);
        
        // Try to find order by ID first, then by public_id
        $order = Order::where('id', $orderId)
            ->orWhere('public_id', $orderId)
            ->first();
        
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'cancelled') {
            return redirect()->route('checkout.success', ['orderId' => $order->id]);
        }

        $paymentMethod = $request->input('payment_method', 'mpesa');

        $newRequest = new Request();
        $newRequest->merge([
            'payment_method' => $paymentMethod,
            'phone_number' => $order->customer_phone ? str_replace('+255', '', $order->customer_phone) : '',
            'email' => $order->customer_email,
            'first_name' => $order->customer_name ? explode(' ', $order->customer_name)[0] ?? '' : '',
            'last_name' => $order->customer_name ? implode(' ', array_slice(explode(' ', $order->customer_name), 1)) : '',
        ]);

        $paymentResult = $this->simulatePayment($order, $newRequest, $paymentMethod);

        if ($paymentResult['success']) {
            $this->deductStock($order);
            $order->update([
                'status' => 'completed',
                'stock_deducted_at' => now(),
            ]);
            return redirect()->route('shop')->with('success', 'Payment successful! Your order has been confirmed.');
        } else {
            return redirect()->route('cart.index')->with('error', 'Payment failed. Please try again.');
        }
    }

    /**
     * ✅ DEDUCT STOCK FOR ORDER
     */
    private function deductStock($order)
    {
        $orderItems = OrderItem::where('order_id', $order->id)->get();
        foreach ($orderItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock -= $item->quantity;
                $product->save();
            }
        }
    }

    /**
     * ✅ SIMULATE PAYMENT - Updated with card support
     */
    private function simulatePayment($order, $request, $paymentMethod = 'mpesa', $cardDetails = null)
    {
        // Generate unique transaction ID
        $transactionId = 'TXN-' . date('Y') . '-' . strtoupper(uniqid());

        // ✅ Payment simulation: 85% success rate
        $successRate = 85;
        $random = rand(1, 100);
        $isSuccess = $random <= $successRate;

        // ✅ Simulate processing time (1-3 seconds)
        sleep(rand(1, 3));

        // ✅ Build payment data
        $paymentData = [
            'order_id' => $order->id,
            'transaction_id' => $transactionId,
            'amount' => $order->total_amount,
            'currency' => $order->currency ?? 'TZS',
            'payment_method' => $paymentMethod,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'customer_name' => $request->first_name . ' ' . $request->last_name,
            'status' => $isSuccess ? 'completed' : 'failed',
            'payment_data' => [
                'simulation' => true,
                'success_rate' => $successRate,
                'random_number' => $random,
                'processed_at' => now()->toDateTimeString(),
            ],
            'paid_at' => $isSuccess ? now() : null,
        ];

        // ✅ Add card details if available
        if ($cardDetails) {
            $paymentData['payment_data']['card_details'] = [
                'card_type' => $cardDetails['card_type'] ?? $paymentMethod,
                'card_number_masked' => $cardDetails['card_number'] ?? '****',
                'card_expiry' => $cardDetails['card_expiry'] ?? '',
                'cardholder_name' => $cardDetails['cardholder_name'] ?? '',
            ];
        }

        // ✅ Create payment record
        $payment = Payment::create($paymentData);

        if ($isSuccess) {
            return [
                'success' => true,
                'message' => 'Payment successful!',
                'transaction_id' => $transactionId,
                'amount' => $order->total_amount,
                'order' => $order,
                'payment' => $payment,
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Payment failed. Please try again with a different payment method.',
                'transaction_id' => $transactionId,
                'amount' => $order->total_amount,
                'order' => $order,
                'payment' => $payment,
            ];
        }
    }

    /**
     * ✅ REFUND STOCK (for manual/admin use)
     */
    private function refundStock($order)
    {
        $orderItems = OrderItem::where('order_id', $order->id)->get();
        foreach ($orderItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }
    }

    /**
     * Get current user's cart
     */
    private function getCart()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->first();
        } else {
            $sessionId = Session::getId();
            return Cart::where('session_id', $sessionId)->first();
        }
    }

    /**
     * Normalize Tanzania phone into local 9 digits
     */
    private function normalizeTzPhoneToLocal9(?string $phone): string
    {
        $digitsOnly = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digitsOnly, '255')) {
            $digitsOnly = substr($digitsOnly, 3);
        }

        if (strlen($digitsOnly) === 10 && str_starts_with($digitsOnly, '0')) {
            $digitsOnly = substr($digitsOnly, 1);
        }

        return $digitsOnly;
    }

    /**
     * Display phone as +255XXXXXXXXX - FIXED: No regex
     */
    private function formatTzPhoneForDisplay(?string $phone): string
    {
        $local9 = $this->normalizeTzPhoneToLocal9($phone);

        // ✅ Simple validation without regex
        if (strlen($local9) !== 9 || !ctype_digit($local9)) {
            return '';
        }

        return '+255' . $local9;
    }
}