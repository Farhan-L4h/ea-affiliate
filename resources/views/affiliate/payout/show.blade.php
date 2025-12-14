<x-app-layout>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Detail Pencairan Komisi') }}
                </h2>
                <a href="{{ route('affiliate.payout.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Payout Information --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>
                            Informasi Pencairan
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">ID Pencairan</label>
                                <p class="mt-1 text-sm font-mono text-gray-900">#{{ str_pad($payout->id, 6, '0', STR_PAD_LEFT) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Jumlah</label>
                                <p class="mt-1 text-xl font-bold text-gray-900">Rp {{ number_format($payout->amount, 0, ',', '.') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Status</label>
                                <p class="mt-1">
                                    @if($payout->status == 'pending')
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> Menunggu Persetujuan
                                        </span>
                                    @elseif($payout->status == 'approved')
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <i class="fas fa-check mr-1"></i> Disetujui
                                        </span>
                                    @elseif($payout->status == 'paid')
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-double mr-1"></i> Sudah Dibayar
                                        </span>
                                    @elseif($payout->status == 'rejected')
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Ditolak
                                        </span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tanggal Pengajuan</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    <i class="far fa-calendar mr-1"></i>
                                    {{ $payout->requested_at ? \Carbon\Carbon::parse($payout->requested_at)->format('d M Y H:i') : '-' }}
                                </p>
                            </div>

                            @if($payout->processed_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Tanggal Diproses</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <i class="far fa-calendar-check mr-1"></i>
                                        {{ \Carbon\Carbon::parse($payout->processed_at)->format('d M Y H:i') }}
                                    </p>
                                </div>
                            @endif

                            @if($payout->admin_note)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Catatan Admin</label>
                                    <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                        <p class="text-sm text-gray-900">{{ $payout->admin_note }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Bank Information --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-university text-blue-600 mr-2"></i>
                            Informasi Rekening
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nama Bank</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payout->affiliate->bank_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nama Pemilik Rekening</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payout->affiliate->account_holder_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nomor Rekening</label>
                                <p class="mt-1 text-sm font-mono text-gray-900">{{ $payout->affiliate->account_number }}</p>
                            </div>
                        </div>

                        @if($payout->status == 'approved')
                            <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Pencairan Anda telah disetujui dan sedang dalam proses transfer ke rekening Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif($payout->status == 'paid')
                            <div class="mt-6 p-4 bg-green-50 border-l-4 border-green-400 rounded">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-700">
                                            Dana telah ditransfer ke rekening Anda. Silakan cek rekening Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Section --}}
            @if($payout->status == 'pending')
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Aksi</h3>
                        <div class="flex space-x-3">
                            <button onclick="cancelPayout({{ $payout->id }})" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                <i class="fas fa-times-circle mr-2"></i>
                                Batalkan Pengajuan
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- SweetAlert Scripts --}}
    <script>
        function cancelPayout(id) {
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: 'Apakah Anda yakin ingin membatalkan pengajuan pencairan ini? Saldo akan dikembalikan ke akun Anda.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Batalkan',
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/payout/${id}/cancel`;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
