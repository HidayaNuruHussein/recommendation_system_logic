@extends('layouts.app')

@section('title', 'Payment Failed - electronicStore')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="mb-3">ayment Failed</h2>
                    <p class="text-muted mb-4">Your payment could not be processed. Please try again.</p>

                    <div class="order-details-box bg-light p-4 rounded-3 mb-4 text-start">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Order Number</small>
                                <strong>{{ $order->order_number ?? $order->public_id }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Amount</small>
                                <strong class="text-danger">Tsh {{ number_format($order->total_amount, 0) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <button onclick="retryPayment()" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-2"></i>Try Again
                        </button>
                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-cart me-2"></i>Return to Cart
                        </a>
                        <a href="{{ route('shop') }}" class="btn btn-outline-primary">
                            <i class="bi bi-shop me-2"></i>Continue Shopping
                        </a>
                    </div>

                    <form id="retryPaymentForm" action="{{ route('checkout.retry', ['order' => $order->id]) }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" name="payment_method" value="{{ $payment->payment_method ?? 'mpesa' }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function retryPayment() {
    document.getElementById('retryPaymentForm').submit();
}
</script>
@endsection