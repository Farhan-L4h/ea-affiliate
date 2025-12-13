@extends('layouts.admin')

@section('title', 'Sale Detail')

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Back Button -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Sale Detail</h2>
                    <p class="text-muted">Detail informasi penjualan</p>
                </div>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Sales
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sale Information -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Sale Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Product</p>
                            <h6 class="mb-0">{{ $sale->product }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Sale Date</p>
                            <h6 class="mb-0">{{ $sale->sale_date->format('d M Y H:i') }}</h6>
                        </div>
                    </div>

                    <hr class="horizontal dark">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Sale Amount</p>
                            <h5 class="mb-0 text-primary">Rp {{ number_format($sale->sale_amount, 0, ',', '.') }}</h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Commission (%)</p>
                            <h5 class="mb-0 text-warning">{{ number_format($sale->commission_percentage, 1) }}%</h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Commission Amount</p>
                            <h5 class="mb-0 text-success">Rp {{ number_format($sale->commission_amount, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Information -->
            <div class="card mt-4">
                <div class="card-header pb-0">
                    <h6>Related Order</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Order ID</p>
                            <h6 class="mb-0">{{ $sale->order->order_id }}</h6>
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
                            <span class="{{ $statusClass[$sale->order->status] ?? 'badge bg-gradient-secondary' }}">
                                {{ ucfirst($sale->order->status) }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <p class="text-sm text-secondary mb-1">Order Date</p>
                            <h6 class="mb-0">{{ $sale->order->created_at->format('d M Y') }}</h6>
                        </div>
                    </div>

                    <hr class="horizontal dark">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Customer</p>
                            @if($sale->order->telegram_username)
                            <a href="https://t.me/{{ $sale->order->telegram_username }}" target="_blank" class="text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                <h6 class="mb-0">{{ $sale->order->telegram_username }}</h6>
                                <i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i>
                            </a>
                            @else
                            <h6 class="mb-0">N/A</h6>
                            @endif
                            <p class="text-xs text-secondary">{{ $sale->order->telegram_chat_id }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Total Payment</p>
                            <h6 class="mb-0">Rp {{ number_format($sale->order->total_amount, 0, ',', '.') }}</h6>
                            <p class="text-xs text-secondary">(Price: Rp {{ number_format($sale->order->price, 0, ',', '.') }} + Code: {{ $sale->order->unique_code }})</p>
                        </div>
                    </div>

                    @if($sale->order->paid_at)
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-sm text-secondary mb-1">Paid At</p>
                            <h6 class="mb-0 text-success">{{ $sale->order->paid_at->format('d M Y H:i:s') }}</h6>
                        </div>
                    </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('admin.orders.show', $sale->order->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>View Order Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Affiliate Information -->
            <div class="card mt-4">
                <div class="card-header pb-0">
                    <h6>Affiliate Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Affiliate Name</p>
                            <h6 class="mb-0">{{ $sale->affiliate->user->name ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Referral Code</p>
                            <h6 class="mb-0">{{ $sale->affiliate->ref_code ?? 'N/A' }}</h6>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Email</p>
                            <h6 class="mb-0">{{ $sale->affiliate->user->email ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm text-secondary mb-1">Telegram</p>
                            @if($sale->affiliate && $sale->affiliate->telegram_username)
                            <a href="https://t.me/{{ $sale->affiliate->telegram_username }}" target="_blank" class="text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                <h6 class="mb-0">{{ $sale->affiliate->telegram_username ?? 'N/A' }}</h6>
                                <i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i>
                            </a>
                            @else
                            <h6 class="mb-0">N/A</h6>
                            @endif
                        </div>
                    </div>

                    @if($sale->affiliate)
                    <div class="mt-3">
                        <a href="{{ route('admin.affiliates.show', $sale->affiliate->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-user me-2"></i>View Affiliate Profile
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Commission & Payout Info -->
        <div class="col-md-4 mb-4">
            <!-- Commission Summary -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Commission Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-sm">Sale Amount:</span>
                            <span class="text-sm font-weight-bold">Rp {{ number_format($sale->sale_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-sm">Commission Rate:</span>
                            <span class="text-sm font-weight-bold text-warning">{{ number_format($sale->commission_percentage, 1) }}%</span>
                        </div>
                        <hr class="horizontal dark my-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-sm font-weight-bold">Total Commission:</span>
                            <span class="text-sm font-weight-bold text-success">Rp {{ number_format($sale->commission_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="alert alert-success text-white" role="alert">
                        <strong>Net Revenue:</strong><br>
                        Rp {{ number_format($sale->sale_amount - $sale->commission_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Payout Status -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Payout Status</h6>
                </div>
                <div class="card-body">
                    @if($sale->payouts->count() > 0)
                        @foreach($sale->payouts as $payout)
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm">Amount:</span>
                                <span class="text-sm font-weight-bold">Rp {{ number_format($payout->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm">Status:</span>
                                @php
                                    $payoutStatusClass = [
                                        'pending' => 'badge bg-gradient-warning',
                                        'approved' => 'badge bg-gradient-info',
                                        'paid' => 'badge bg-gradient-success',
                                        'rejected' => 'badge bg-gradient-danger',
                                    ];
                                @endphp
                                <span class="{{ $payoutStatusClass[$payout->status] ?? 'badge bg-gradient-secondary' }}">
                                    {{ ucfirst($payout->status) }}
                                </span>
                            </div>
                            @if($payout->paid_at)
                            <div class="text-xs text-secondary mt-2">
                                Paid: {{ $payout->paid_at->format('d M Y H:i') }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-secondary mb-0">Belum ada data payout untuk sale ini</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
