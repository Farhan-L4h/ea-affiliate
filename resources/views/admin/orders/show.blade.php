@extends('layouts.admin')

@section('title', 'Order Detail')

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Back Button -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Order Detail</h2>
                    <p class="text-muted">Detail informasi order #{{ $order->order_id }}</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Order Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Order ID</p>
                            <h6 class="mb-0">{{ $order->order_id }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Status</p>
                            @php
                                $statusClass = [
                                    'pending' => 'badge bg-gradient-warning',
                                    'paid' => 'badge bg-gradient-success',
                                    'expired' => 'badge bg-gradient-danger',
                                    'cancelled' => 'badge bg-gradient-secondary',
                                ];
                            @endphp
                            <span class="{{ $statusClass[$order->status] ?? 'badge bg-gradient-secondary' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Order Date</p>
                            <h6 class="mb-0">{{ $order->created_at->format('d M Y H:i') }}</h6>
                        </div>
                    </div>

                    <hr class="horizontal dark">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Product</p>
                            <h6 class="mb-0">{{ $order->product }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Price</p>
                            <h6 class="mb-0">Rp {{ number_format($order->price, 0, ',', '.') }}</h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Unique Code</p>
                            <h6 class="mb-0 text-primary">{{ $order->unique_code }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Total Payment</p>
                            <h5 class="mb-0 text-success">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h5>
                        </div>
                    </div>

                    @if($order->expires_at)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p class="text-sm text-secondary mb-1">Payment Expiry</p>
                            <h6 class="mb-0 {{ $order->expires_at < now() ? 'text-danger' : '' }}">
                                {{ $order->expires_at->format('d M Y H:i') }}
                                @if($order->expires_at < now())
                                <span class="badge badge-sm bg-gradient-danger ms-2">Expired</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    @endif

                    @if($order->paid_at)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p class="text-sm text-secondary mb-1">Paid At</p>
                            <h6 class="mb-0 text-success">{{ $order->paid_at->format('d M Y H:i:s') }}</h6>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mt-4">
                <div class="card-header pb-0">
                    <h6>Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Telegram Chat ID</p>
                            <h6 class="mb-0">{{ $order->telegram_chat_id }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Telegram Username</p>
                            <h6 class="mb-0">{{ $order->telegram_username ?? 'N/A' }}</h6>
                        </div>
                    </div>

                    @if($order->affiliate_ref)
                    <hr class="horizontal dark">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-sm text-secondary mb-1">Referral Code</p>
                            <h6 class="mb-0">
                                {{ $order->affiliate_ref }}
                                @if($order->affiliate)
                                <span class="text-sm text-secondary">
                                    ({{ $order->affiliate->user->name ?? 'Unknown' }})
                                </span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions & Status Update -->
        <div class="col-md-4 mb-4">
            <!-- Status Update -->
            @if($order->status == 'pending')
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Update Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Change Status</label>
                            <select name="status" class="form-control" required>
                                <option value="paid">Mark as Paid</option>
                                <option value="cancelled">Cancel Order</option>
                                <option value="expired">Mark as Expired</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Payment Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-sm text-secondary mb-1">Bank Account</p>
                        <h6 class="mb-0">BCA</h6>
                        <p class="text-sm mb-0">0111502977</p>
                        <p class="text-xs text-secondary">a.n. Udin Nurwachid</p>
                    </div>

                    @if($order->moota_mutation_id)
                    <hr class="horizontal dark">
                    <div class="mb-3">
                        <p class="text-sm text-secondary mb-1">Moota Mutation ID</p>
                        <p class="text-sm font-weight-bold mb-0">{{ $order->moota_mutation_id }}</p>
                    </div>
                    @endif

                    @if($order->payment_verified_at)
                    <hr class="horizontal dark">
                    <div class="mb-3">
                        <p class="text-sm text-secondary mb-1">Payment Verified At</p>
                        <p class="text-sm mb-0">{{ $order->payment_verified_at->format('d M Y H:i:s') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger">
                <div class="card-header pb-0 bg-transparent">
                    <h6 class="text-danger">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
