@extends('layouts.admin')

@section('title', 'Sale Detail')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header with Back Button -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Sale Detail</h2>
                <p class="text-gray-600">Detail informasi penjualan</p>
            </div>
            <a href="{{ route('admin.sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Sales
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sale Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Sale Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Product</p>
                            <h6 class="font-semibold text-gray-900">{{ $sale->product }}</h6>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Sale Date</p>
                            <h6 class="font-semibold text-gray-900">{{ $sale->sale_date->format('d M Y H:i') }}</h6>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Sale Amount</p>
                            <h5 class="text-xl font-bold text-blue-600">Rp {{ number_format($sale->sale_amount, 0, ',', '.') }}</h5>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Commission (%)</p>
                            <h5 class="text-xl font-bold text-yellow-600">{{ number_format($sale->commission_percentage, 1) }}%</h5>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Commission Amount</p>
                            <h5 class="text-xl font-bold text-green-600">Rp {{ number_format($sale->commission_amount, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Related Order</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Order ID</p>
                            <h6 class="font-semibold text-gray-900">{{ $sale->order->order_id }}</h6>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Status</p>
                            @php
                                $statusClass = [
                                    'pending' => 'px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded',
                                    'paid' => 'px-2 py-1 bg-green-100 text-green-800 text-xs rounded',
                                    'expired' => 'px-2 py-1 bg-red-100 text-red-800 text-xs rounded',
                                    'cancelled' => 'px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded',
                                ];
                            @endphp
                            <span class="{{ $statusClass[$sale->order->status] ?? 'px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded' }}">
                                {{ ucfirst($sale->order->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Order Date</p>
                            <h6 class="font-semibold text-gray-900">{{ $sale->order->created_at->format('d M Y') }}</h6>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Customer</p>
                            @if($sale->order->telegram_username)
                            <a href="https://t.me/{{ $sale->order->telegram_username }}" target="_blank" class="text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                <h6 class="font-semibold">{{ $sale->order->telegram_username }}</h6>
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                            @else
                            <h6 class="font-semibold text-gray-900">N/A</h6>
                            @endif
                            <p class="text-xs text-gray-500">{{ $sale->order->telegram_chat_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Payment</p>
                            <h6 class="font-semibold text-gray-900">Rp {{ number_format($sale->order->total_amount, 0, ',', '.') }}</h6>
                            <p class="text-xs text-gray-500">(Price: Rp {{ number_format($sale->order->base_amount, 0, ',', '.') }} + Code: {{ $sale->order->unique_code }})</p>
                        </div>
                    </div>

                    @if($sale->order->paid_at)
                    <div class="mb-6">
                        <p class="text-sm text-gray-500 mb-1">Paid At</p>
                        <h6 class="font-semibold text-green-600">{{ $sale->order->paid_at->format('d M Y H:i:s') }}</h6>
                    </div>
                    @endif

                    <div>
                        <a href="{{ route('admin.orders.show', $sale->order->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-external-link-alt mr-2"></i>View Order Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Affiliate Information -->
            @if($sale->affiliate)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Affiliate Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Affiliate Name</p>
                            <h6 class="font-semibold text-gray-900">{{ $sale->affiliate->user->name ?? 'N/A' }}</h6>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Referral Code</p>
                            <h6 class="font-semibold text-gray-900">{{ $sale->affiliate->ref_code ?? 'N/A' }}</h6>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Email</p>
                            <h6 class="font-semibold text-gray-900">{{ $sale->affiliate->user->email ?? 'N/A' }}</h6>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Telegram</p>
                            @if($sale->affiliate->telegram_username)
                            <a href="https://t.me/{{ $sale->affiliate->telegram_username }}" target="_blank" class="text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                <h6 class="font-semibold">{{ $sale->affiliate->telegram_username }}</h6>
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                            @else
                            <h6 class="font-semibold text-gray-900">N/A</h6>
                            @endif
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('admin.affiliates.show', $sale->affiliate->user_id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-user mr-2"></i>View Affiliate Profile
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Affiliate Information</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-500">Pembelian langsung tanpa affiliate</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Commission & Payout Info -->
        <div class="space-y-6">
            <!-- Commission Summary -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Commission Summary</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Sale Amount:</span>
                            <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($sale->sale_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Commission Rate:</span>
                            <span class="text-sm font-semibold text-yellow-600">{{ number_format($sale->commission_percentage, 1) }}%</span>
                        </div>
                        <hr class="border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-900">Total Commission:</span>
                            <span class="text-sm font-bold text-green-600">Rp {{ number_format($sale->commission_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-green-800">Net Revenue:</p>
                        <p class="text-lg font-bold text-green-600">Rp {{ number_format($sale->sale_amount - $sale->commission_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Payout Status -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Payout Status</h3>
                </div>
                <div class="p-6">
                    @if($sale->payouts->count() > 0)
                        @foreach($sale->payouts as $payout)
                        <div class="mb-4 pb-4 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Amount:</span>
                                <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($payout->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Status:</span>
                                @php
                                    $payoutStatusClass = [
                                        'pending' => 'px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded',
                                        'approved' => 'px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded',
                                        'paid' => 'px-2 py-1 bg-green-100 text-green-800 text-xs rounded',
                                        'rejected' => 'px-2 py-1 bg-red-100 text-red-800 text-xs rounded',
                                    ];
                                @endphp
                                <span class="{{ $payoutStatusClass[$payout->status] ?? 'px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded' }}">
                                    {{ ucfirst($payout->status) }}
                                </span>
                            </div>
                            @if($payout->paid_at)
                            <div class="text-xs text-gray-500 mt-2">
                                Paid: {{ $payout->paid_at->format('d M Y H:i') }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500">Belum ada data payout untuk sale ini</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
