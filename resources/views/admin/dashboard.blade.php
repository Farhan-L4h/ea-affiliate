@extends('layouts.admin')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Affiliates --}}
        <a href="{{ route('admin.affiliates.index') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Affiliates</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalAffiliates }}</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-check-circle mr-1"></i>{{ $activeAffiliates }} Aktif
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-blue-600"></i>
                </div>
            </div>
        </a>

        {{-- Prospects --}}
        <a href="{{ route('admin.prospects.index') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Prospek</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalProspects }}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        {{ $totalJoinedChannel }} Join Channel
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-friends text-2xl text-green-600"></i>
                </div>
            </div>
        </a>

        {{-- Sales --}}
        <a href="{{ route('admin.prospects.index', ['status' => 'purchased']) }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Penjualan</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalSales }}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-2xl text-yellow-600"></i>
                </div>
            </div>
        </a>

        {{-- Commission --}}
        <a href="{{ route('admin.prospects.index', ['status' => 'purchased']) }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Komisi</p>
                    <p class="text-3xl font-bold text-gray-800">Rp {{ number_format($totalCommission, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        <span class="text-green-600">Dibayar: Rp {{ number_format($paidCommission, 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-2xl text-purple-600"></i>
                </div>
            </div>
        </a>
    </div>

    {{-- Orders Stats --}}
    <div class="bg-white rounded-lg shadow p-4 lg:p-6">
        <h3 class="text-base lg:text-lg font-semibold mb-4">Status Orders</h3>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <a href="{{ route('admin.orders.index') }}" class="text-center p-4 bg-gray-50 rounded hover:bg-gray-100 transition-colors cursor-pointer">
                <p class="text-2xl font-bold text-gray-600">{{ $totalOrders }}</p>
                <p class="text-sm text-gray-600">Total Orders</p>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-center p-4 bg-yellow-50 rounded hover:bg-yellow-100 transition-colors cursor-pointer">
                <p class="text-2xl font-bold text-yellow-600">{{ $pendingOrders }}</p>
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-xs text-gray-500">{{ $totalOrders > 0 ? round(($pendingOrders / $totalOrders) * 100, 1) : 0 }}%</p>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="text-center p-4 bg-green-50 rounded hover:bg-green-100 transition-colors cursor-pointer">
                <p class="text-2xl font-bold text-green-600">{{ $paidOrders }}</p>
                <p class="text-sm text-gray-600">Paid</p>
                <p class="text-xs text-gray-500">{{ $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 1) : 0 }}%</p>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'expired']) }}" class="text-center p-4 bg-red-50 rounded hover:bg-red-100 transition-colors cursor-pointer">
                <p class="text-2xl font-bold text-red-600">{{ $expiredOrders }}</p>
                <p class="text-sm text-gray-600">Expired</p>
                <p class="text-xs text-gray-500">{{ $totalOrders > 0 ? round(($expiredOrders / $totalOrders) * 100, 1) : 0 }}%</p>
            </a>
        </div>
    </div>

    {{-- Monthly Revenue Chart --}}
    <div class="bg-white rounded-lg shadow p-4 lg:p-6">
        <h3 class="text-base lg:text-lg font-semibold mb-4">Revenue Bulanan (6 Bulan Terakhir)</h3>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6">
        {{-- Top Affiliates --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 lg:p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Top 10 Affiliates</h3>
                <a href="{{ route('admin.affiliates.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="pb-2">#</th>
                            <th class="pb-2">Nama</th>
                            <th class="pb-2">Ref Code</th>
                            <th class="pb-2 text-right">Prospek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topAffiliates as $index => $affiliate)
                        <tr class="border-b hover:bg-gray-50 cursor-pointer"
                            ondblclick="window.location='{{ route('admin.affiliates.show', $affiliate->user) }}'"
                            title="Double click untuk melihat detail">
                            <td class="py-2">{{ $index + 1 }}</td>
                            <td class="py-2">{{ $affiliate->user->name }}</td>
                            <td class="py-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                    {{ $affiliate->ref_code }}
                                </span>
                            </td>
                            <td class="py-2 text-right font-semibold">{{ $affiliate->referral_tracks_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 lg:p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Orders Terbaru</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="pb-2">Order ID</th>
                            <th class="pb-2">Customer</th>
                            <th class="pb-2">Amount</th>
                            <th class="pb-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr class="border-b hover:bg-gray-50 cursor-pointer"
                            ondblclick="window.location='{{ route('admin.orders.show', $order) }}'"
                            title="Double click untuk melihat detail">
                            <td class="py-2 text-xs">{{ $order->order_id }}</td>
                            <td class="py-2">
                                @if($order->telegram_username)
                                <a href="https://t.me/{{ $order->telegram_username }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1" onclick="event.stopPropagation();">
                                    {{ $order->telegram_username }}
                                    <i class="fas fa-external-link-alt" style="font-size: 0.6rem;"></i>
                                </a>
                                @else
                                N/A
                                @endif
                            </td>
                            <td class="py-2 font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="py-2">
                                @if($order->status === 'pending')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Pending</span>
                                @elseif($order->status === 'paid')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Paid</span>
                                @elseif($order->status === 'expired')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Expired</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Sales --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 lg:p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Sales Terbaru</h3>
                <a href="{{ route('admin.sales.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="pb-2">Tanggal</th>
                            <th class="pb-2">Affiliate</th>
                            <th class="pb-2">Amount</th>
                            <th class="pb-2">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr class="border-b hover:bg-gray-50 cursor-pointer"
                            ondblclick="window.location='{{ route('admin.sales.show', $sale) }}'"
                            title="Double click untuk melihat detail">
                            <td class="py-2 text-xs">{{ $sale->sale_date->format('d/m H:i') }}</td>
                            <td class="py-2">{{ $sale->affiliate->user->name ?? 'N/A' }}</td>
                            <td class="py-2 font-semibold">Rp {{ number_format($sale->sale_amount, 0, ',', '.') }}</td>
                            <td class="py-2 text-green-600 font-semibold">Rp {{ number_format($sale->commission_amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">Belum ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Monthly Revenue Chart
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($monthlyRevenue->pluck('month')),
                datasets: [{
                    label: 'Revenue',
                    data: @json($monthlyRevenue->pluck('revenue')),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
