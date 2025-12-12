@extends('layouts.admin')

@section('page-title', 'Order Detail')

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold mb-1">Order Detail</h2>
            <p class="text-gray-600 text-sm">Detail informasi order #{{ $order->order_id }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Orders</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Order Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Order ID</p>
                            <p class="font-semibold">{{ $order->order_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Status</p>
                            @php
                                $statusClass = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'expired' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Order Date</p>
                            <p class="font-semibold">{{ $order->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="border-t pt-6 mb-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Product</p>
                                <p class="font-semibold">{{ $order->product }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Price</p>
                                <p class="font-semibold">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6 mb-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Unique Code</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $order->unique_code }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Total Payment</p>
                                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($order->expires_at)
                    <div class="border-t pt-6 {{ $order->paid_at ? 'mb-6' : '' }}">
                        <p class="text-sm text-gray-500 mb-1">Payment Expiry</p>
                        <p class="font-semibold {{ $order->expires_at < now() ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $order->expires_at->format('d M Y H:i') }}
                            @if($order->expires_at < now())
                            <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Expired</span>
                            @endif
                        </p>
                    </div>
                    @endif

                    @if($order->paid_at)
                    <div class="border-t pt-6">
                        <p class="text-sm text-gray-500 mb-1">Paid At</p>
                        <p class="font-semibold text-green-600">{{ $order->paid_at->format('d M Y H:i:s') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Customer Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Telegram Chat ID</p>
                            <p class="font-semibold">{{ $order->telegram_chat_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Telegram Username</p>
                            @if($order->telegram_username)
                            <a href="https://t.me/{{ $order->telegram_username }}" target="_blank" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                {{ $order->telegram_username }}
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                            @else
                            <p class="font-semibold text-gray-400">N/A</p>
                            @endif
                        </div>
                    </div>

                    @if($order->affiliate_ref)
                    <div class="border-t pt-6">
                        <p class="text-sm text-gray-500 mb-1">Referral Code</p>
                        <p class="font-semibold">
                            {{ $order->affiliate_ref }}
                            @if($order->affiliate)
                            <span class="text-sm text-gray-500 font-normal">
                                ({{ $order->affiliate->user->name ?? 'Unknown' }})
                            </span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions & Status Update -->
        <div class="space-y-6">
            <!-- Status Update -->
            @if($order->status == 'pending')
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Update Status</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Change Status</label>
                            <select name="status" class="w-full rounded-md border-gray-300" required>
                                <option value="paid">Mark as Paid</option>
                                <option value="cancelled">Cancel Order</option>
                                <option value="expired">Mark as Expired</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Payment Details -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Payment Details</h3>
                </div>
                <div class="p-6">
                    <div class="{{ $order->moota_mutation_id || $order->payment_verified_at ? 'mb-6' : '' }}">
                        <p class="text-sm text-gray-500 mb-2">Bank Account</p>
                        <p class="font-semibold text-lg">BCA</p>
                        <p class="font-semibold">0111502977</p>
                        <p class="text-xs text-gray-500">a.n. Udin Nurwachid</p>
                    </div>

                    @if($order->moota_mutation_id)
                    <div class="border-t pt-6 {{ $order->payment_verified_at ? 'mb-6' : '' }}">
                        <p class="text-sm text-gray-500 mb-1">Moota Mutation ID</p>
                        <p class="font-semibold">{{ $order->moota_mutation_id }}</p>
                    </div>
                    @endif

                    @if($order->payment_verified_at)
                    <div class="border-t pt-6">
                        <p class="text-sm text-gray-500 mb-1">Payment Verified At</p>
                        <p class="font-semibold">{{ $order->payment_verified_at->format('d M Y H:i:s') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-lg shadow border-2 border-red-200">
                <div class="p-6 border-b bg-red-50">
                    <h3 class="text-lg font-semibold text-red-600">Danger Zone</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center justify-center gap-2">
                            <i class="fas fa-trash"></i>
                            <span>Delete Order</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
