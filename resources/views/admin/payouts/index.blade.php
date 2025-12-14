@extends('layouts.admin')

@section('page-title', 'Kelola Pencairan Komisi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-1">Kelola Pencairan Komisi</h2>
        <p class="text-gray-600 text-sm">Kelola pengajuan pencairan komisi dari affiliate</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Semua</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingCount + $approvedCount + $paidCount + $rejectedCount }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-lg text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Menunggu</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">50%</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-lg text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Disetujui</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $approvedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">50%</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-lg text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Dibayar</p>
                    <p class="text-2xl font-bold text-green-600">{{ $paidCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">50%</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-double text-lg text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600">{{ $rejectedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">0%</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times-circle text-lg text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow mb-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6">
                <a href="{{ route('admin.payouts.index', ['status' => 'all']) }}" 
                   class="border-b-2 py-4 px-1 text-sm font-medium {{ $status == 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Semua
                    @if($pendingCount + $approvedCount + $paidCount + $rejectedCount > 0)
                        <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $status == 'all' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $pendingCount + $approvedCount + $paidCount + $rejectedCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.payouts.index', ['status' => 'pending']) }}" 
                   class="border-b-2 py-4 px-1 text-sm font-medium {{ $status == 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Menunggu
                    @if($pendingCount > 0)
                        <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $status == 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.payouts.index', ['status' => 'approved']) }}" 
                   class="border-b-2 py-4 px-1 text-sm font-medium {{ $status == 'approved' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Disetujui
                    @if($approvedCount > 0)
                        <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $status == 'approved' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $approvedCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.payouts.index', ['status' => 'paid']) }}" 
                   class="border-b-2 py-4 px-1 text-sm font-medium {{ $status == 'paid' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Dibayar
                    @if($paidCount > 0)
                        <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $status == 'paid' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $paidCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.payouts.index', ['status' => 'rejected']) }}" 
                   class="border-b-2 py-4 px-1 text-sm font-medium {{ $status == 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Ditolak
                    @if($rejectedCount > 0)
                        <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $status == 'rejected' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ $rejectedCount }}
                        </span>
                    @endif
                </a>
            </nav>
        </div>
    </div>

    {{-- Payouts Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($payouts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Affiliate
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal Pengajuan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bank
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($payouts as $payout)
                            <tr class="hover:bg-gray-50 cursor-pointer" ondblclick="viewDetail({{ $payout->id }})">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $payout->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $payout->affiliate->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $payout->affiliate->ref_code }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payout->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    Rp {{ number_format($payout->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $payout->affiliate->bank_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $payout->affiliate->account_number }}</div>
                                    <div class="text-xs text-gray-400">{{ $payout->affiliate->account_holder_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($payout->status == 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Menunggu
                                        </span>
                                    @elseif($payout->status == 'approved')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Disetujui
                                        </span>
                                    @elseif($payout->status == 'paid')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Dibayar
                                        </span>
                                    @elseif($payout->status == 'rejected')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="event.stopPropagation(); viewDetail({{ $payout->id }})" 
                                            class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                        <i class="fas fa-eye text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $payouts->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-gray-400 text-5xl mb-3"></i>
                <p class="text-gray-500">Tidak ada data pencairan</p>
            </div>
        @endif
    </div>
</div>

    {{-- Approve Modal --}}
    <script>
        function approveModal(id) {
            Swal.fire({
                title: 'Setujui Pengajuan?',
                html: '<textarea id="admin_note" class="swal2-textarea w-full p-2 border rounded" placeholder="Catatan (opsional)" rows="3"></textarea>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Setujui',
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
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
                html: '<textarea id="admin_note" class="swal2-textarea w-full p-2 border rounded" placeholder="Alasan penolakan (wajib diisi)" rows="3" required></textarea>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-times-circle mr-2"></i>Ya, Tolak',
                cancelButtonText: '<i class="fas fa-arrow-left mr-2"></i>Batal',
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
                html: '<textarea id="admin_note" class="swal2-textarea w-full p-2 border rounded" placeholder="Catatan transfer (opsional)" rows="3"></textarea>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-check-double mr-2"></i>Ya, Sudah Dibayar',
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
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

        // View detail - simple alert for now
        function viewDetail(id) {
            // You can implement modal or redirect to detail page
            window.location.href = `/admin/payouts/${id}`;
        }
    </script>
@endsection
