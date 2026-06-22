<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CheckoutInformation;
use App\Models\Order;
use App\Models\OrderItem;
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
        // Check if user is authenticated
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

        // Calculate totals
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
     * Process the checkout
     */
    public function store(Request $request)
    {
        // Normalize phone number:
        // accepts 0XXXXXXXXX, XXXXXXXXX, 255XXXXXXXXX, or +255XXXXXXXXX
        $rawPhone = (string) $request->input('phone_number', '');
        $digitsOnly = $this->normalizeTzPhoneToLocal9($rawPhone);
        $request->merge(['phone_number' => $digitsOnly]);

        try {
            // Validate required info form
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'required|string|regex:/^[0-9]{9}$/',
                'save_required_information' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON validation errors for AJAX requests
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

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'currency' => 'TZS',
                'ordered_at' => now(),

                // Store customer info directly on order
                'customer_name' => $request->first_name . ' ' . $request->last_name,
                'customer_email' => $request->email,
                'customer_phone' => $formattedPhone,
                'stock_deducted_at' => null,
            ]);

            // Create order items and update stock
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

            // Clear the cart
            $cart->cartItems()->delete();
            $cart->delete();

            DB::commit();

            // Always return JSON for this AJAX endpoint
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully! Your order ' . ($order->order_number ?: $order->public_id) . ' has been confirmed.',
                'order_id' => $order->public_id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Checkout failed while creating order', [
                'user_id' => Auth::id(),
                'email' => $request->email,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Always return JSON for this AJAX endpoint
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order. Please try again.'
            ], 500);
        }
    }

    /**
     * Show checkout success page
     */
    public function success(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('orderItems.product', 'orderAddresses');

        return view('shop.checkout-success', compact('order'));
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
     * Normalize Tanzania phone into local 9 digits (e.g. 622070303).
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
     * Display phone as +255XXXXXXXXX for checkout UI.
     */
    private function formatTzPhoneForDisplay(?string $phone): string
    {
        $local9 = $this->normalizeTzPhoneToLocal9($phone);

        if (!preg_match('/^[0-9]{9}$/', $local9)) {
            return '';
        }

        return '+255' . $local9;
    }
}
