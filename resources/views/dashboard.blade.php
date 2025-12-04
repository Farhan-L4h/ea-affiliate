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
                        
                        <p class="text-sm text-gray-500 mt-1">
                            Link Telegram langsung:
                            https://t.me/{{ config('services.telegram.username') }}?start={{ $affiliate->ref_code }}
                        </p>
                        <a href="https://t.me/{{ config('services.telegram.username') }}?start={{ $affiliate->ref_code }}" class="inline-flex items-center px-3 py-2 mt-2 rounded-md bg-emerald-600 text-white text-xs font-semibold">Copy / Share Link Telegram</a>
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
                    <h2 class="text-lg font-semibold mt-8 mb-2">Data Prospek / Referral</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border table-auto table-striped">
                                <thead>
                                    <tr class="bg-gray-100 ">
                                        <th class="px-3 py-2 border text-start">Tanggal</th>
                                        <th class="px-3 py-2 border text-start">Username</th>
                                        <th class="px-3 py-2 border text-start">Email</th>
                                        <th class="px-3 py-2 border text-start">Nomor</th>
                                        <th class="px-3 py-2 border text-start">Status</th>
                                        <th class="px-3 py-2 border text-start">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($leads as $lead)
                                        <tr>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->created_at?->format('d-m-Y H:i') }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->prospect_telegram_username ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->prospect_email ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->prospect_phone ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                @switch($lead->status)
                                                    @case('joined_bot')
                                                        Join Bot
                                                        @break
                                                    @case('purchased')
                                                        Sudah Beli
                                                        @break
                                                    @default
                                                        Klik Link
                                                @endswitch
                                            </td>

                                            <td class="px-3 py-2 border">
                                                @if ($lead->prospect_telegram_id)
                                                    Dari Telegram
                                                @elseif ($lead->prospect_email)
                                                    Dari Website
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-2 border text-center text-gray-500">
                                                Belum ada prospek.
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