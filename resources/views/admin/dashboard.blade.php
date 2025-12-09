@extends('layouts.admin')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

    {{-- Conversion Stats --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Konversi Prospek</h3>
        <div class="grid grid-cols-3 gap-4">
            <a href="{{ route('admin.prospects.index', ['status' => 'clicked']) }}" class="text-center p-4 bg-blue-50 rounded hover:bg-blue-100 transition-colors cursor-pointer">
                <p class="text-2xl font-bold text-blue-600">{{ $totalClicked }}</p>
                <p class="text-sm text-gray-600">Klik Link</p>
                <p class="text-xs text-gray-500">{{ $totalProspects > 0 ? round(($totalClicked / $totalProspects) * 100, 1) : 0 }}%</p>
            </a>
            <a href="{{ route('admin.prospects.index', ['status' => 'joined_channel']) }}" class="text-center p-4 bg-green-50 rounded hover:bg-green-100 transition-colors cursor-pointer">
                <p class="text-2xl font-bold text-green-600">{{ $totalJoinedChannel }}</p>
                <p class="text-sm text-gray-600">Join Channel</p>
                <p class="text-xs text-gray-500">{{ $totalProspects > 0 ? round(($totalJoinedChannel / $totalProspects) * 100, 1) : 0 }}%</p>
            </a>
            <a href="{{ route('admin.prospects.index', ['status' => 'purchased']) }}" class="text-center p-4 bg-purple-50 rounded hover:bg-purple-100 transition-colors cursor-pointer">
                <p class="text-2xl font-bold text-purple-600">{{ $totalPurchased }}</p>
                <p class="text-sm text-gray-600">Sudah Beli</p>
                <p class="text-xs text-gray-500">{{ $totalProspects > 0 ? round(($totalPurchased / $totalProspects) * 100, 1) : 0 }}%</p>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Affiliates --}}
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b flex items-center justify-between">
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

        {{-- Recent Prospects --}}
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Prospek Terbaru</h3>
                <a href="{{ route('admin.prospects.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="p-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="pb-2">Tanggal</th>
                            <th class="pb-2">Username</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2">Affiliate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProspects as $prospect)
                        <tr class="border-b hover:bg-gray-50 cursor-pointer"
                            ondblclick="window.location='{{ route('admin.prospects.show', $prospect) }}'"
                            title="Double click untuk melihat detail">
                            <td class="py-2 text-xs">{{ $prospect->created_at->format('d/m H:i') }}</td>
                            <td class="py-2">{{ $prospect->prospect_telegram_username ?? $prospect->prospect_name ?? '-' }}</td>
                            <td class="py-2">
                                @if($prospect->status === 'clicked')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Klik</span>
                                @elseif($prospect->status === 'joined_channel')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Join</span>
                                @else
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Beli</span>
                                @endif
                            </td>
                            <td class="py-2 text-xs">{{ $prospect->affiliate->user->name ?? '-' }}</td>
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
@endsection
