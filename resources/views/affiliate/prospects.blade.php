@section('title', 'Data Prospek')

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold">Data Prospek / Leads</h2>
                            <p class="text-sm text-gray-600">Kelola semua prospek dari referral Anda</p>
                        </div>
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>

                    {{-- Filter & Search Form --}}
                    <form method="GET" action="{{ route('affiliate.prospects') }}" class="mb-4 p-4 bg-gray-50 rounded-lg">
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
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status"
                                    class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="clicked" {{ request('status') == 'clicked' ? 'selected' : '' }}>Klik Link</option>
                                    <option value="joined_channel" {{ request('status') == 'joined_channel' ? 'selected' : '' }}>Join Channel</option>
                                    <option value="purchased" {{ request('status') == 'purchased' ? 'selected' : '' }}>Sudah Beli</option>
                                </select>
                            </div>

                            {{-- Date From --}}
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                    class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Date To --}}
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                    class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center gap-2">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                                    <i class="fas fa-search mr-2"></i>Filter
                                </button>
                                <a href="{{ route('affiliate.prospects') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">
                                    <i class="fas fa-redo mr-2"></i>Reset
                                </a>
                            </div>

                            {{-- Per Page Selector --}}
                            <div class="flex items-center gap-2">
                                <label for="per_page" class="text-sm text-gray-600">Per Page:</label>
                                <select name="per_page" id="per_page" onchange="this.form.submit()"
                                    class="text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    {{-- Table --}}
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
                                @forelse($leads as $index => $lead)
                                    <tr class="hover:bg-gray-50 cursor-pointer transition-colors" 
                                        ondblclick="showProspectDetail({{ $lead->id }})"
                                        title="Double-click untuk lihat detail">
                                        <td class="px-4 py-3 text-sm">{{ $leads->firstItem() + $index }}</td>
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
                                        <td class="px-4 py-3 text-sm">{{ Str::limit($lead->notes ?? '-', 30) }}</td>
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

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $leads->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div id="detail-modal" class="fixed inset-0 z-50 bg-black/40 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between px-5 py-3 border-b">
                    <h3 class="text-lg font-semibold">Detail Prospek</h3>
                    <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div id="modal-content" class="px-5 py-4">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                        <p class="mt-2 text-gray-500">Memuat data...</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-lg">
                    <button type="button" onclick="closeDetailModal()" class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showProspectDetail(id) {
            document.getElementById('detail-modal').classList.remove('hidden');
            
            fetch(`/prospects/${id}`)
                .then(response => response.json())
                .then(data => {
                    let statusBadge = '';
                    if (data.status === 'clicked') {
                        statusBadge = '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Klik Link</span>';
                    } else if (data.status === 'joined_channel') {
                        statusBadge = '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Join Channel</span>';
                    } else {
                        statusBadge = '<span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Sudah Beli</span>';
                    }

                    document.getElementById('modal-content').innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Username</label>
                                <p class="text-sm text-gray-900">${data.prospect_telegram_username || data.prospect_name || '-'}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Telegram ID</label>
                                <p class="text-sm text-gray-900">${data.prospect_telegram_id || '-'}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Email</label>
                                <p class="text-sm text-gray-900">${data.prospect_email || '-'}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Nomor Telepon</label>
                                <p class="text-sm text-gray-900">${data.prospect_phone || '-'}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">IP Address</label>
                                <p class="text-sm text-gray-900">${data.prospect_ip || '-'}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <p class="text-sm">${statusBadge}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tanggal Klik</label>
                                <p class="text-sm text-gray-900">${new Date(data.created_at).toLocaleString('id-ID')}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Last Update</label>
                                <p class="text-sm text-gray-900">${new Date(data.updated_at).toLocaleString('id-ID')}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700">Keterangan</label>
                                <p class="text-sm text-gray-900">${data.notes || '-'}</p>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    document.getElementById('modal-content').innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-circle text-3xl text-red-400"></i>
                            <p class="mt-2 text-red-500">Gagal memuat data</p>
                        </div>
                    `;
                });
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
