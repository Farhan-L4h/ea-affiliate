@extends('layouts.admin')

@section('page-title', 'Manage Orders')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-1">Manage Orders</h2>
        <p class="text-gray-600 text-sm">Kelola semua order pembelian</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-lg text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending']) }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-lg text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Paid</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['paid']) }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-lg text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Expired</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['expired']) }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times-circle text-lg text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 col-span-2 sm:col-span-3 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Total Revenue</p>
                    <p class="text-xl font-bold text-purple-600">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-lg text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" class="w-full rounded-md border-gray-300" placeholder="Order ID, Username, Chat ID" value="{{ request('search') }}">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" class="w-full rounded-md border-gray-300" value="{{ request('date_from') }}">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" class="w-full rounded-md border-gray-300" value="{{ request('date_to') }}">
            </div>
            <div class="md:col-span-3 flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Reset</a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 lg:p-6 border-b">
            <h3 class="text-lg font-semibold">Orders List</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left border-b">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Order ID</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Customer</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Product</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Amount</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase text-center">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr class="border-b hover:bg-gray-50 cursor-pointer" ondblclick="window.location='{{ route('admin.orders.show', $order->id) }}'" title="Double click untuk melihat detail">
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $order->order_id }}</div>
                            @if($order->affiliate_ref)
                            <div class="text-xs text-gray-500">Ref: {{ $order->affiliate_ref }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $order->telegram_username ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $order->telegram_chat_id }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $order->product }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">(+{{ $order->unique_code }})</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusClass = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'expired' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusClass[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $order->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data order
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
