@extends('layouts.admin')

@section('page-title', 'Manage Prospects')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h3 class="text-xl lg:text-2xl font-bold">Data Prospek / Leads</h3>
        <p class="text-xs lg:text-sm text-gray-600">Kelola semua prospek dari semua affiliate</p>
    </div>

    {{-- Filter Form --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Username, email, phone..." class="w-full rounded-md border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">Semua Status</option>
                    <option value="clicked" {{ request('status') === 'clicked' ? 'selected' : '' }}>Klik Link</option>
                    <option value="started" {{ request('status') === 'started' ? 'selected' : '' }}>Started</option>
                    <option value="order_created" {{ request('status') === 'order_created' ? 'selected' : '' }}>Order Dibuat</option>
                    <option value="joined_channel" {{ request('status') === 'joined_channel' ? 'selected' : '' }}>Join Channel</option>
                    <option value="purchased" {{ request('status') === 'purchased' ? 'selected' : '' }}>Sudah Beli</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Affiliate</label>
                <select name="affiliate" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">Semua Affiliate</option>
                    @foreach($affiliates as $aff)
                    <option value="{{ $aff->ref_code }}" {{ request('affiliate') === $aff->ref_code ? 'selected' : '' }}>
                        {{ $aff->user->name }} ({{ $aff->ref_code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 text-sm">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mt-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <a href="{{ route('admin.prospects.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
                <button type="button" onclick="toggleBulkDelete()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                    <i class="fas fa-trash-alt mr-1"></i><span class="hidden sm:inline">Hapus Massal</span>
                </button>
                <button type="button" id="delete-selected-btn" onclick="deleteSelected()" class="hidden px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-800 text-sm">
                    <i class="fas fa-check mr-1"></i>Hapus Terpilih (<span id="selected-count">0</span>)
                </button>
                <button type="button" id="cancel-bulk-btn" onclick="toggleBulkDelete()" class="hidden px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs lg:text-sm text-gray-600">Per Page:</label>
                <select name="per_page" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-3 py-3 text-left checkbox-column hidden">
                        <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="px-3 py-3 text-left">#</th>
                    <th class="px-3 py-3 text-left">Tanggal</th>
                    <th class="px-3 py-3 text-left">Username</th>
                    <th class="px-3 py-3 text-left">Email</th>
                    <th class="px-3 py-3 text-left">Phone</th>
                    <th class="px-3 py-3 text-left">Status</th>
                    <th class="px-3 py-3 text-left">Affiliate</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prospects as $index => $prospect)
                <tr class="border-b hover:bg-gray-50 cursor-pointer"
                    ondblclick="window.location='{{ route('admin.prospects.show', $prospect) }}'"
                    title="Double click untuk melihat detail">
                    <td class="px-3 py-3 checkbox-column hidden">
                        <input type="checkbox" value="{{ $prospect->id }}" class="prospect-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" onchange="updateSelectedCount()" onclick="event.stopPropagation()">
                    </td>
                    <td class="px-3 py-3">{{ $prospects->firstItem() + $index }}</td>
                    <td class="px-3 py-3 text-xs">
                        {{ $prospect->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-3 py-3">{{ $prospect->prospect_telegram_username ?? $prospect->prospect_name ?? '-' }}</td>
                    <td class="px-3 py-3 truncate">{{ $prospect->prospect_email ?? '-' }}</td>
                    <td class="px-3 py-3">{{ $prospect->prospect_phone ?? '-' }}</td>
                    <td class="px-3 py-3">
                        @if($prospect->status === 'clicked' || $prospect->status === 'started')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Klik Link</span>
                        @elseif($prospect->status === 'order_created')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Order Dibuat</span>
                        @elseif($prospect->status === 'joined_channel')
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Join Channel</span>
                        @elseif($prospect->status === 'purchased')
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Sudah Beli</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">{{ ucfirst($prospect->status) }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                            {{ $prospect->affiliate->user->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <a href="{{ route('admin.prospects.show', $prospect) }}" class="text-blue-600 hover:text-blue-800" title="Detail" onclick="event.stopPropagation()">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada data prospek
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $prospects->links() }}
    </div>
</div>

@push('scripts')
<script>
let bulkDeleteMode = false;

function toggleBulkDelete() {
    bulkDeleteMode = !bulkDeleteMode;
    const checkboxColumns = document.querySelectorAll('.checkbox-column');
    const deleteBtn = document.getElementById('delete-selected-btn');
    const cancelBtn = document.getElementById('cancel-bulk-btn');
    
    checkboxColumns.forEach(col => {
        col.classList.toggle('hidden');
    });
    
    if (bulkDeleteMode) {
        deleteBtn.classList.remove('hidden');
        cancelBtn.classList.remove('hidden');
    } else {
        deleteBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        // Uncheck all
        document.getElementById('select-all').checked = false;
        document.querySelectorAll('.prospect-checkbox').forEach(cb => cb.checked = false);
        updateSelectedCount();
    }
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.prospect-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.prospect-checkbox:checked').length;
    document.getElementById('selected-count').textContent = selectedCount;
    
    // Update select-all checkbox
    const allCheckboxes = document.querySelectorAll('.prospect-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.prospect-checkbox:checked');
    document.getElementById('select-all').checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
}

function deleteSelected() {
    const selectedIds = Array.from(document.querySelectorAll('.prospect-checkbox:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Ada Pilihan',
            text: 'Silakan pilih setidaknya satu prospek untuk dihapus',
        });
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Hapus Massal',
        html: `Apakah Anda yakin ingin menghapus <strong>${selectedIds.length}</strong> prospek yang dipilih?<br><br><span class="text-red-600">Data yang dihapus tidak dapat dikembalikan!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.prospects.bulk-delete") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
