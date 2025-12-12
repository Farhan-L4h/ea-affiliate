@extends('layouts.admin')

@section('page-title', 'Manage Sales')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-1">Manage Sales</h2>
        <p class="text-gray-600 text-sm">Kelola data penjualan dan komisi</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Sales</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Sales Amount</p>
                    <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Commission</p>
                    <p class="text-2xl font-bold text-yellow-600">Rp {{ number_format($stats['total_commission'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-percent text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Net Revenue</p>
                    <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($stats['net_revenue'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.sales.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" class="w-full rounded-md border-gray-300" placeholder="Order ID, Affiliate Name" value="{{ request('search') }}">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" class="w-full rounded-md border-gray-300" value="{{ request('date_from') }}">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" class="w-full rounded-md border-gray-300" value="{{ request('date_to') }}">
            </div>
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.sales.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Reset</a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 lg:p-6 border-b">
            <h3 class="text-lg font-semibold">Sales List</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left border-b">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Order</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Affiliate</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Product</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Sale Amount</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Commission</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Sale Date</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr class="border-b hover:bg-gray-50 cursor-pointer" ondblclick="window.location='{{ route('admin.sales.show', $sale->id) }}'" title="Double click untuk melihat detail">
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $sale->order->order_id }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->order->telegram_username ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $sale->affiliate->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->affiliate->ref_code }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $sale->product }}</td>
                        <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($sale->sale_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="font-semibold text-green-600">Rp {{ number_format($sale->commission_amount, 0, ',', '.') }}</div>
                            <div class="text-xs">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded">{{ number_format($sale->commission_percentage, 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $sale->sale_date->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->sale_date->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.sales.show', $sale->id) }}" class="text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data sales
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
