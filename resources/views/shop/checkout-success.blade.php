@extends('layouts.app')

@section('title', 'Payment Successful - electronicStore')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="mb-3">Payment Successful!</h2>
                    <p class="text-muted mb-4">Thank you for your order. Your payment has been processed successfully.</p>

                    <div class="order-details-box bg-light p-4 rounded-3 mb-4 text-start">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Order Number</small>
                                <strong>{{ $order->order_number ?? $order->public_id }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Amount Paid</small>
                                <strong class="text-success">Tsh {{ number_format($order->total_amount, 0) }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Transaction ID</small>
                                <strong>{{ $payment->transaction_id ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Payment Method</small>
                                <strong>{{ ucfirst($payment->payment_method ?? 'N/A') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ route('shop') }}" class="btn btn-outline-primary">
                            <i class="bi bi-shop me-2"></i>Continue Shopping
                        </a>
                        <a href="{{ route('customer.orders') }}" class="btn btn-primary">
                            <i class="bi bi-box me-2"></i>View Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection