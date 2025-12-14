@extends('layouts.admin')

@section('page-title', 'Detail Pencairan Komisi')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.payouts.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Pencairan
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-semibold mb-1">Detail Pencairan #{{ $payout->id }}</h2>
                <p class="text-gray-600 text-sm">Tanggal Pengajuan: {{ $payout->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                @if($payout->status == 'pending')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        Menunggu
                    </span>
                @elseif($payout->status == 'approved')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        Disetujui
                    </span>
                @elseif($payout->status == 'paid')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        Dibayar
                    </span>
                @elseif($payout->status == 'rejected')
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        Ditolak
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informasi Pencairan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Pencairan</h3>
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-500">Jumlah Pencairan</label>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($payout->amount, 0, ',', '.') }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Status</label>
                    <p class="font-medium text-gray-900">
                        @if($payout->status == 'pending')
                            Menunggu Persetujuan
                        @elseif($payout->status == 'approved')
                            Disetujui - Menunggu Transfer
                        @elseif($payout->status == 'paid')
                            Sudah Dibayar
                        @elseif($payout->status == 'rejected')
                            Ditolak
                        @endif
                    </p>
                </div>

                @if($payout->admin_note)
                <div>
                    <label class="text-sm text-gray-500">Catatan Admin</label>
                    <p class="text-gray-900">{{ $payout->admin_note }}</p>
                </div>
                @endif

                @if($payout->processed_at)
                <div>
                    <label class="text-sm text-gray-500">Diproses Pada</label>
                    <p class="text-gray-900">{{ $payout->processed_at->format('d M Y H:i') }}</p>
                </div>
                @endif

                @if($payout->processor)
                <div>
                    <label class="text-sm text-gray-500">Diproses Oleh</label>
                    <p class="text-gray-900">{{ $payout->processor->name }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Informasi Affiliate -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Affiliate</h3>
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-500">Nama Affiliate</label>
                    <p class="font-medium text-gray-900">{{ $payout->affiliate->user->name ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Kode Referral</label>
                    <p class="font-medium text-gray-900">{{ $payout->affiliate->ref_code }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">Email</label>
                    <p class="text-gray-900">{{ $payout->affiliate->user->email ?? 'N/A' }}</p>
                </div>

                <div class="border-t pt-3 mt-3">
                    <h4 class="font-medium text-gray-900 mb-2">Informasi Rekening Bank</h4>
                    
                    <div class="bg-gray-50 p-3 rounded">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="text-gray-500">Nama Bank:</div>
                            <div class="font-medium">{{ $payout->affiliate->bank_name }}</div>
                            
                            <div class="text-gray-500">Nama Pemilik:</div>
                            <div class="font-medium">{{ $payout->affiliate->account_holder_name }}</div>
                            
                            <div class="text-gray-500">Nomor Rekening:</div>
                            <div class="font-medium">{{ $payout->affiliate->account_number }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($payout->status == 'pending')
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Tindakan</h3>
        <div class="flex gap-3">
            <button onclick="approveModal({{ $payout->id }})" 
                    class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-check-circle mr-2"></i>
                Setujui Pengajuan
            </button>
            
            <button onclick="rejectModal({{ $payout->id }})" 
                    class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <i class="fas fa-times-circle mr-2"></i>
                Tolak Pengajuan
            </button>
        </div>
        <p class="text-sm text-gray-500 mt-3">
            <i class="fas fa-info-circle mr-1"></i>
            Pastikan untuk memeriksa informasi rekening bank sebelum menyetujui.
        </p>
    </div>
    @elseif($payout->status == 'approved')
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Tindakan</h3>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-800">Pengajuan Sudah Disetujui</h4>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Silakan lakukan transfer manual ke rekening:</p>
                        <div class="mt-2 bg-white p-3 rounded border border-blue-200">
                            <p><strong>Bank:</strong> {{ $payout->affiliate->bank_name }}</p>
                            <p><strong>Nomor Rekening:</strong> {{ $payout->affiliate->account_number }}</p>
                            <p><strong>Nama Pemilik:</strong> {{ $payout->affiliate->account_holder_name }}</p>
                            <p class="mt-2"><strong>Jumlah:</strong> <span class="text-lg font-bold">Rp {{ number_format($payout->amount, 0, ',', '.') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <button onclick="markAsPaidModal({{ $payout->id }})" 
                class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-check-double mr-2"></i>
            Tandai Sudah Dibayar
        </button>
        
        <p class="text-sm text-gray-500 mt-3">
            <i class="fas fa-info-circle mr-1"></i>
            Klik tombol ini setelah transfer berhasil dilakukan.
        </p>
    </div>
    @elseif($payout->status == 'paid')
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-green-800">Pencairan Sudah Selesai</h3>
                <div class="mt-2 text-sm text-green-700">
                    <p>Pembayaran telah dikonfirmasi pada {{ $payout->processed_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
    @elseif($payout->status == 'rejected')
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-400 text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-red-800">Pengajuan Ditolak</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>Ditolak pada {{ $payout->processed_at->format('d M Y H:i') }}</p>
                    @if($payout->admin_note)
                    <p class="mt-1"><strong>Alasan:</strong> {{ $payout->admin_note }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- SweetAlert Scripts --}}
<script>
    function approveModal(id) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            html: `
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                    <textarea id="admin_note" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Masukkan catatan..." rows="4"></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Setujui',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
            customClass: {
                popup: 'swal-wide',
                htmlContainer: 'swal-html-container'
            },
            preConfirm: () => {
                return {
                    note: document.getElementById('admin_note').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/payouts/${id}/approve`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'POST';
                form.appendChild(methodField);
                
                if (result.value.note) {
                    const noteField = document.createElement('input');
                    noteField.type = 'hidden';
                    noteField.name = 'admin_note';
                    noteField.value = result.value.note;
                    form.appendChild(noteField);
                }
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function rejectModal(id) {
        Swal.fire({
            title: 'Tolak Pengajuan?',
            html: `
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan (wajib) <span class="text-red-500">*</span></label>
                    <textarea id="admin_note" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Masukkan alasan penolakan..." rows="4" required></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-times-circle mr-2"></i>Ya, Tolak',
            cancelButtonText: '<i class="fas fa-arrow-left mr-2"></i>Batal',
            customClass: {
                popup: 'swal-wide',
                htmlContainer: 'swal-html-container'
            },
            preConfirm: () => {
                const note = document.getElementById('admin_note').value;
                if (!note || note.trim() === '') {
                    Swal.showValidationMessage('Alasan penolakan wajib diisi');
                    return false;
                }
                return { note: note }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/payouts/${id}/reject`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'POST';
                form.appendChild(methodField);
                
                const noteField = document.createElement('input');
                noteField.type = 'hidden';
                noteField.name = 'admin_note';
                noteField.value = result.value.note;
                form.appendChild(noteField);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function markAsPaidModal(id) {
        Swal.fire({
            title: 'Tandai Sebagai Dibayar?',
            html: `
                <div class="text-left">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Transfer (opsional)</label>
                    <textarea id="admin_note" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan catatan transfer..." rows="4"></textarea>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-check-double mr-2"></i>Ya, Sudah Dibayar',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
            customClass: {
                popup: 'swal-wide',
                htmlContainer: 'swal-html-container'
            },
            preConfirm: () => {
                return {
                    note: document.getElementById('admin_note').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/payouts/${id}/mark-paid`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'POST';
                form.appendChild(methodField);
                
                if (result.value.note) {
                    const noteField = document.createElement('input');
                    noteField.type = 'hidden';
                    noteField.name = 'admin_note';
                    noteField.value = result.value.note;
                    form.appendChild(noteField);
                }
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<style>
    .swal-wide {
        width: 600px !important;
    }
    .swal-html-container {
        margin: 1em 0 !important;
    }
</style>
@endsection
