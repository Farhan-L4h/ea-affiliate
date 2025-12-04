<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Affiliate Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Referral Info --}}
                    <div class="mb-4">
                        <p><strong>Referral Code:</strong> {{ $affiliate->ref_code }}</p>
                        <p class="text-sm text-gray-500">
                            Gunakan link: {{ url('/?ref=' . $affiliate->ref_code) }}
                        </p>
                    </div>

                    {{-- Stat Cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mb-6">
                        <div class="p-4 border rounded">
                            <div class="text-sm text-gray-500">Total Clicks</div>
                            <div class="text-2xl font-bold">{{ $totalClicks }}</div>
                        </div>
                        <div class="p-4 border rounded">
                            <div class="text-sm text-gray-500">Total Join Channel</div>
                            <div class="text-2xl font-bold">{{ $totalJoins }}</div>
                        </div>
                        <div class="p-4 border rounded">
                            <div class="text-sm text-gray-500">Total Sales</div>
                            <div class="text-2xl font-bold">{{ $totalSales }}</div>
                        </div>
                        <div class="p-4 border rounded">
                            <div class="text-sm text-gray-500">Total Komisi</div>
                            <div class="text-2xl font-bold">
                                Rp {{ number_format($totalCommission, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Recent Sales Table --}}
                    <h2 class="text-lg font-semibold mb-2">Riwayat Penjualan Terbaru</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-3 py-2 border">Tanggal</th>
                                    <th class="px-3 py-2 border">Produk</th>
                                    <th class="px-3 py-2 border">Amount</th>
                                    <th class="px-3 py-2 border">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSales as $sale)
                                    <tr>
                                        <td class="px-3 py-2 border">
                                            {{ $sale->created_at->format('d-m-Y H:i') }}
                                        </td>
                                        <td class="px-3 py-2 border">{{ $sale->product }}</td>
                                        <td class="px-3 py-2 border">
                                            Rp {{ number_format($sale->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 border">{{ $sale->status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-2 border text-center text-gray-500">
                                            Belum ada penjualan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
