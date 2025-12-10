@section('title', 'Dashboard Affiliate')

<x-app-layout>
    {{-- <x-slot name="header">
    </x-slot> --}}

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-2">
                Affiliate Dashboard
            </h2>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Referral Info --}}
                    <div class="mb-4">
                        <p><strong>Referral Code:</strong> {{ $affiliate->ref_code }}</p>

                        {{-- Link utama yang dibagikan (melewati website dulu) --}}
                        <div class="flex items-center gap-3 mt-2">
                            <p class="text-sm text-gray-500 mr-1">
                                Link Affiliate:
                                <span class="font-mono text-xs">
                                    {{ url('/r?ref=' . $affiliate->ref_code) }}
                                </span>
                            </p>

                            <button type="button"
                                onclick="copyToClipboard('{{ url('/r?ref=' . $affiliate->ref_code) }}')"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 text-xs font-semibold hover:text-gray-700 hover:bg-gray-100">
                                <i class="fa fa-copy text-sm"></i>
                            </button>
                        </div>

                        {{-- (Opsional) tetap tampilkan link Telegram langsung kalau mau informasi saja --}}
                        <p class="text-xs text-gray-400 mt-1">
                            Link langsung ke bot:
                            https://t.me/{{ config('services.telegram.username') }}?start={{ $affiliate->ref_code }}
                        </p>
                    </div>

                    {{-- Boleh taruh di bawah file / pakai @push kalau layout mendukung --}}
                    <script>
                        function copyToClipboard(text) {
                            navigator.clipboard.writeText(text).then(() => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Link berhasil dicopy ke clipboard',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }).catch(err => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Gagal copy link',
                                    showConfirmButton: true
                                });
                                console.error('Gagal copy:', err);
                            });
                        }
                    </script>



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

                    {{-- Recent Prospects Table --}}
                    <div class="flex items-center justify-between mt-8 mb-4">
                        <h2 class="text-lg font-semibold">10 Prospek Terbaru</h2>
                        <a href="{{ route('affiliate.prospects') }}" class="text-sm text-blue-600 hover:text-blue-700">
                            Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($recentLeads as $index => $lead)
                                    <tr class="hover:bg-gray-50 cursor-pointer transition-colors" 
                                        ondblclick="window.location='{{ route('affiliate.prospects') }}'"
                                        title="Double-click untuk lihat semua prospek">
                                        <td class="px-4 py-3 text-sm">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $lead->created_at->format('d-m-Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ $lead->prospect_telegram_username ?? $lead->prospect_name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $lead->prospect_email ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $lead->prospect_phone ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($lead->status === 'clicked')
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Klik Link</span>
                                            @elseif($lead->status === 'joined_channel')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Join Channel</span>
                                            @else
                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Sudah Beli</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $lead->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                            Belum ada data prospek
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Note untuk affiliate --}}
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Catatan:</strong> Double-click pada row prospek untuk melihat semua data. Data prospek hanya dapat diedit oleh admin.
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
