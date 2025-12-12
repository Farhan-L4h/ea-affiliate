@extends('layouts.admin')

@section('page-title', 'Detail Prospek')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl lg:text-2xl font-bold">Detail Prospek</h3>
            <p class="text-xs lg:text-sm text-gray-600">Informasi lengkap prospek</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <button onclick="openEditModal()" class="flex-1 sm:flex-none px-3 lg:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                <i class="fas fa-edit mr-1 lg:mr-2"></i><span class="hidden sm:inline">Edit</span>
            </button>
            <button onclick="confirmDelete()" class="flex-1 sm:flex-none px-3 lg:px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                <i class="fas fa-trash mr-1 lg:mr-2"></i><span class="hidden sm:inline">Hapus</span>
            </button>
            <a href="{{ route('admin.prospects.index') }}" class="flex-1 sm:flex-none text-center px-3 lg:px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                <i class="fas fa-arrow-left mr-1 lg:mr-2"></i><span class="hidden sm:inline">Kembali</span>
            </a>
        </div>
    </div>

    {{-- Prospect Info --}}
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 space-y-8">
            {{-- Personal Information Section --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4 pb-2 border-b flex items-center">
                    <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                    Informasi Prospek
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Username / Nama</label>
                        <br>
                        @if($prospect->prospect_telegram_username)
                        <a href="https://t.me/{{ $prospect->prospect_telegram_username }}" target="_blank" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline mt-1 inline-flex items-center gap-1">
                            {{ $prospect->prospect_telegram_username }}
                            <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                        @else
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $prospect->prospect_name ?? '-' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Telegram ID</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $prospect->prospect_telegram_id ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">IP Address</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $prospect->prospect_ip ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Email</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1 break-all">{{ $prospect->prospect_email ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Nomor Telepon</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $prospect->prospect_phone ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Status</label>
                        <div class="mt-1">
                            @if($prospect->status === 'clicked')
                                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                    <i class="fas fa-mouse-pointer mr-1.5"></i>Klik Link
                                </span>
                            @elseif($prospect->status === 'joined_channel')
                                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                    <i class="fas fa-users mr-1.5"></i>Join Channel
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                                    <i class="fas fa-shopping-cart mr-1.5"></i>Sudah Beli
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tracking Information Section --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4 pb-2 border-b flex items-center">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                    Informasi Tracking
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Affiliate</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            @if($prospect->affiliate)
                                <a href="{{ route('admin.affiliates.show', $prospect->affiliate->user) }}" class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center">
                                    <i class="fas fa-external-link-alt mr-2 text-xs"></i>
                                    {{ $prospect->affiliate->user->name }} <span class="text-gray-500 ml-1">({{ $prospect->ref_code }})</span>
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Tanggal Klik</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                            {{ $prospect->created_at->format('d F Y, H:i') }} WIB
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Terakhir Diperbarui</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            <i class="fas fa-clock text-gray-400 mr-2"></i>
                            {{ $prospect->updated_at->format('d F Y, H:i') }} WIB
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 uppercase tracking-wide">Keterangan</label>
                        <div class="mt-2 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            @if($prospect->notes)
                                <p class="text-sm text-gray-900 break-all leading-relaxed">{{ $prospect->notes }}</p>
                            @else
                                <p class="text-sm text-gray-400 italic">Tidak ada keterangan</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/40 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="flex items-center justify-between px-5 py-3 border-b">
                <h3 class="text-lg font-semibold">Edit Prospek</h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form id="edit-form" method="POST" action="{{ route('admin.prospects.update', $prospect) }}">
                @csrf
                @method('PATCH')

                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $prospect->prospect_telegram_username ?? $prospect->prospect_name ?? '-' }}</p>
                    </div>

                    <div>
                        <label for="modal_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="prospect_email" id="modal_email" value="{{ $prospect->prospect_email }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    </div>

                    <div>
                        <label for="modal_phone" class="block text-sm font-medium text-gray-700">Nomor HP</label>
                        <input type="text" name="prospect_phone" id="modal_phone" value="{{ $prospect->prospect_phone }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                    </div>

                    <div>
                        <label for="modal_status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="modal_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                            <option value="clicked" {{ $prospect->status === 'clicked' ? 'selected' : '' }}>Klik Link</option>
                            <option value="joined_channel" {{ $prospect->status === 'joined_channel' ? 'selected' : '' }}>Join Channel</option>
                            <option value="purchased" {{ $prospect->status === 'purchased' ? 'selected' : '' }}>Sudah Beli</option>
                        </select>
                    </div>

                    <div>
                        <label for="modal_notes" class="block text-sm font-medium text-gray-700">Keterangan</label>
                        <textarea name="notes" id="modal_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm">{{ $prospect->notes }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-lg">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Form (hidden) --}}
<form id="delete-form" action="{{ route('admin.prospects.destroy', $prospect) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function openEditModal() {
    document.getElementById('edit-modal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
}

function confirmDelete() {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus prospek ini? Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}

// Submit form dengan AJAX
document.getElementById('edit-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat mengupdate data',
        });
    });
});
</script>
@endpush
@endsection
