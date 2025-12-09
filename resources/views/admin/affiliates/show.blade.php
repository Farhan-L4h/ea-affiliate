@extends('layouts.admin')

@section('page-title', 'Detail Affiliate')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold">{{ $affiliate->name }}</h3>
            <p class="text-sm text-gray-600">Detail informasi affiliate</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.affiliates.edit', $affiliate) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <button onclick="toggleStatus()" class="px-4 py-2 {{ $affiliate->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg">
                <i class="fas fa-{{ $affiliate->is_active ? 'ban' : 'check' }} mr-2"></i>{{ $affiliate->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
            @if(!$affiliate->is_active)
            <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>Hapus
            </button>
            @endif
            <a href="{{ route('admin.affiliates.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Personal Info --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold mb-4">Informasi Personal</h4>
            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-500">Nama Lengkap</label>
                    <p class="font-medium">{{ $affiliate->name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Nomor Telepon</label>
                    <p class="font-medium">{{ $affiliate->phone }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Email</label>
                    <p class="font-medium">{{ $affiliate->email ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Status Akun</label>
                    <p>
                        @if($affiliate->is_active)
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">
                            <i class="fas fa-check-circle mr-1"></i>Aktif
                        </span>
                        @else
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">
                            <i class="fas fa-times-circle mr-1"></i>Nonaktif
                        </span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Terdaftar Sejak</label>
                    <p class="font-medium">{{ $affiliate->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Affiliate Info --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold mb-4">Informasi Affiliate</h4>
            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-500">Referral Code</label>
                    <p class="font-mono text-lg font-bold text-blue-600">{{ $affiliate->affiliate->ref_code ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Link Affiliate</label>
                    @if($affiliate->affiliate)
                    <div class="mt-1 p-2 bg-gray-50 rounded border">
                        <code class="text-xs">{{ url('/r?ref=' . $affiliate->affiliate->ref_code) }}</code>
                    </div>
                    @else
                    <p>-</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Clicks</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_clicks'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Join Channel</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['total_joined'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Purchased</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['total_purchased'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Total Komisi</p>
            <p class="text-lg font-bold text-yellow-600">Rp {{ number_format($stats['total_commission'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Komisi Dibayar</p>
            <p class="text-lg font-bold text-green-600">Rp {{ number_format($stats['paid_commission'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Recent Prospects --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h4 class="text-lg font-semibold">Prospek Terbaru</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Username</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Phone</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($affiliate->affiliate->referralTracks()->latest()->take(10)->get() as $prospect)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs">{{ $prospect->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $prospect->prospect_telegram_username ?? $prospect->prospect_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $prospect->prospect_email ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $prospect->prospect_phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($prospect->status === 'clicked')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Klik Link</span>
                            @elseif($prospect->status === 'joined_channel')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Join Channel</span>
                            @else
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Sudah Beli</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada prospek</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatus() {
    const isActive = {{ $affiliate->is_active ? 'true' : 'false' }};
    const actionText = isActive ? 'nonaktifkan' : 'aktifkan';
    
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin ${actionText} affiliate ini?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isActive ? '#d97706' : '#059669',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Ya, ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}!`,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.affiliates.toggle-status', $affiliate) }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PATCH';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmDelete() {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: '<p class="text-gray-700">Apakah Anda yakin ingin menghapus affiliate ini?</p><p class="text-sm text-red-600 mt-2">Data yang dihapus tidak dapat dikembalikan!</p>',
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
</script>
@endpush

{{-- Delete Form (hidden) --}}
<form id="delete-form" action="{{ route('admin.affiliates.destroy', $affiliate) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@endsection
