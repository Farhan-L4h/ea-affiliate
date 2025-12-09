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

                    {{-- Recent Sales Table --}}
                    <div class="flex items-center justify-between mt-8 mb-4">
                        <h2 class="text-lg font-semibold">Data Prospek / Referral</h2>

                        {{-- Per Page Selector --}}
                        <div class="flex items-center gap-2">
                            <label for="per_page" class="text-sm text-gray-600">Tampilkan:</label>
                            <select id="per_page" onchange="changePerPage(this.value)"
                                class="text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span class="text-sm text-gray-600">data</span>
                        </div>
                    </div>

                    {{-- Filter & Search Form --}}
                    <form method="GET" action="{{ route('dashboard') }}" class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            {{-- Search --}}
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Username, email, nomor..."
                                    class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Status Filter --}}
                            <div>
                                <label for="status"
                                    class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status"
                                    class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="clicked" {{ request('status') == 'clicked' ? 'selected' : '' }}>Klik
                                        Link</option>
                                    <option value="joined_channel"
                                        {{ request('status') == 'joined_channel' ? 'selected' : '' }}>Join Chanel
                                    </option>
                                    <option value="purchased" {{ request('status') == 'purchased' ? 'selected' : '' }}>
                                        Sudah Beli</option>
                                </select>
                            </div>

                            {{-- Date From --}}
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dari
                                    Tanggal</label>
                                <input type="date" name="date_from" id="date_from"
                                    value="{{ request('date_from') }}"
                                    class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Date To --}}
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Sampai
                                    Tanggal</label>
                                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                    class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-search mr-2"></i>
                                Filter
                            </button>
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">
                                <i class="fas fa-redo mr-2"></i>
                                Reset
                            </a>
                            @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                                <span class="text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Filter aktif
                                </span>
                            @endif
                        </div>

                        {{-- Keep per_page when filtering --}}
                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    </form>

                    <script>
                        function changePerPage(perPage) {
                            const url = new URL(window.location.href);
                            url.searchParams.set('per_page', perPage);
                            url.searchParams.delete('page'); // reset ke halaman 1
                            window.location.href = url.toString();
                        }
                    </script>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border table-fixed">
                            <thead>
                                <tr class="bg-gray-100 ">
                                    <th class="px-3 py-2 border text-start w-5">#</th>
                                    <th class="px-3 py-2 border text-start ">Tanggal</th>
                                    <th class="px-3 py-2 border text-start ">Username</th>
                                    <th class="px-3 py-2 border text-start ">Email</th>
                                    <th class="px-3 py-2 border text-start ">Nomor</th>
                                    <th class="px-3 py-2 border text-start ">Status</th>
                                    <th class="px-3 py-2 border text-start">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leads as $index => $lead)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 border data">{{ $leads->firstItem() + $index }}</td>
                                        <td class="px-3 py-2 border">
                                            @if ($lead->created_at)
                                                {{ $lead->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 border">
                                            {{ $lead->prospect_telegram_username ?? ($lead->prospect_name ?? '-') }}
                                        </td>
                                        <td class="px-3 py-2 border truncate">
                                            {{ $lead->prospect_email ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 border truncate">
                                            {{ $lead->prospect_phone ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 border">

                                            @switch($lead->status)
                                                @case('clicked')
                                                    Klik Link
                                                @break

                                                @case('joined_channel')
                                                    Join Chanel
                                                @break

                                                @case('purchased')
                                                    Sudah Beli
                                                @break

                                                @default
                                                    -
                                            @endswitch
                                        </td>


                                        <td class="px-3 py-2 border truncate"
                                            title="@if ($lead->notes) {{ $lead->notes }}@else{{ $lead->prospect_telegram_id ? 'Dari Telegram' : ($lead->prospect_email ? 'Dari Website' : '-') }} @endif">
                                            @if ($lead->notes)
                                                {{ Str::limit($lead->notes, 20) }}
                                            @else
                                                @if ($lead->prospect_telegram_id)
                                                    Dari Telegram
                                                @elseif ($lead->prospect_email)
                                                    Dari Website
                                                @else
                                                    -
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-3 py-2 border text-center text-gray-500">
                                                Belum ada prospek.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4">
                            {{ $leads->links() }}
                        </div>


                    </div>
                </div>
            </div>
        </div>

        {{-- Note untuk affiliate: Edit hanya bisa dilakukan oleh admin --}}
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Catatan:</strong> Data prospek hanya dapat diedit oleh admin. Jika ada perubahan yang perlu dilakukan, silakan hubungi admin.
        </div>

    </x-app-layout>
