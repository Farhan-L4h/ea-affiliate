@section('title', 'Pencairan Komisi')

<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                Pencairan Komisi
            </h2>

            {{-- Alert Messages with SweetAlert --}}
            @if (session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: '{{ session('success') }}',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    });
                </script>
            @endif

            @if (session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: '{{ session('error') }}',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        });
                    });
                </script>
            @endif

            {{-- Balance Info --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-2">Total Komisi</div>
                    <div class="text-2xl font-bold text-gray-800">
                        Rp {{ number_format($affiliate->total_commission, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-2">Saldo Tersedia</div>
                    <div class="text-2xl font-bold text-green-600">
                        Rp {{ number_format($affiliate->available_balance, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-2">Total Ditarik</div>
                    <div class="text-2xl font-bold text-blue-600">
                        Rp {{ number_format($affiliate->withdrawn_balance, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Bank Information --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Informasi Rekening Bank</h3>
                        <a href="{{ route('affiliate.payout.bank-info') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </div>

                    @if($affiliate->hasBankInfo())
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Nama Bank</div>
                                <div class="font-medium">{{ $affiliate->bank_name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Nama Pemilik Rekening</div>
                                <div class="font-medium">{{ $affiliate->account_holder_name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Nomor Rekening</div>
                                <div class="font-medium">{{ $affiliate->account_number }}</div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        Data bank belum lengkap
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>Silakan lengkapi data bank Anda untuk dapat mengajukan pencairan komisi.</p>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('affiliate.payout.bank-info') }}" 
                                           class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                                            Lengkapi Data Bank
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Payout Request Form --}}
            @if($affiliate->hasBankInfo())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Ajukan Pencairan Komisi</h3>
                        
                        <form method="POST" action="{{ route('affiliate.payout.request') }}" id="payoutForm">
                            @csrf
                            <div class="mb-4">
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jumlah Pencairan
                                </label>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 py-2 border-gray-300 bg-gray-50 text-gray-500 text-base">
                                        Rp
                                    </span>
                                    <input type="text" 
                                           id="amount_display" 
                                           class="flex-1 rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-base"
                                           placeholder="Minimal 10.000"
                                           autocomplete="off">
                                    <input type="hidden" name="amount" id="amount" required>
                                </div>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Saldo tersedia: Rp {{ number_format($affiliate->available_balance, 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" 
                                           id="withdrawAll" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">
                                        <i class="fas fa-wallet mr-1"></i>
                                        Cairkan Semua Saldo (Rp {{ number_format($affiliate->available_balance, 0, ',', '.') }})
                                    </span>
                                </label>
                            </div>

                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50"
                                    {{ $affiliate->available_balance < 10000 ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane mr-2"></i>
                                Ajukan Pencairan
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Payout History --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Riwayat Pencairan</h3>

                    @if($payoutHistory->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jumlah
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Keterangan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payoutHistory as $payout)
                                        <tr class="cursor-pointer hover:bg-gray-50" ondblclick="viewDetail({{ $payout->id }})">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payout->created_at->format('d M Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                Rp {{ number_format($payout->amount, 0, ',', '.') }}
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
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $payout->admin_note ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button onclick="event.stopPropagation(); viewDetail({{ $payout->id }})" class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    Detail
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-gray-400 text-5xl mb-3"></i>
                            <p class="text-gray-500">Belum ada riwayat pencairan</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for Rupiah Formatter and Withdraw All --}}
    <script>
        const availableBalance = {{ $affiliate->available_balance }};
        const amountDisplay = document.getElementById('amount_display');
        const amountHidden = document.getElementById('amount');
        const withdrawAllCheckbox = document.getElementById('withdrawAll');
        const payoutForm = document.getElementById('payoutForm');

        // Format number to Rupiah
        function formatRupiah(angka) {
            if (!angka) return '';
            const number_string = angka.toString().replace(/[^,\d]/g, '');
            const split = number_string.split(',');
            const sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            const ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            
            if (ribuan) {
                const separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // Parse formatted rupiah back to number
        function parseRupiah(rupiah) {
            return parseInt(rupiah.replace(/\./g, '')) || 0;
        }

        // Format input on keyup
        amountDisplay.addEventListener('keyup', function(e) {
            withdrawAllCheckbox.checked = false;
            const value = this.value;
            this.value = formatRupiah(value);
            amountHidden.value = parseRupiah(this.value);
        });

        // Withdraw all checkbox
        withdrawAllCheckbox.addEventListener('change', function() {
            if (this.checked) {
                amountDisplay.value = formatRupiah(availableBalance.toString());
                amountHidden.value = availableBalance;
            } else {
                amountDisplay.value = '';
                amountHidden.value = '';
            }
        });

        // Validate before submit
        payoutForm.addEventListener('submit', function(e) {
            const amount = parseInt(amountHidden.value);
            
            if (!amount || amount < 10000) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Jumlah minimal pencairan adalah Rp 10.000',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f59e0b'
                });
                return false;
            }
            
            if (amount > availableBalance) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Saldo Tidak Cukup',
                    text: 'Jumlah melebihi saldo tersedia',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
                return false;
            }
        });

        // Cancel payout with SweetAlert
        function cancelPayout(id) {
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: 'Apakah Anda yakin ingin membatalkan pengajuan pencairan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak'
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

        // View detail payout
        function viewDetail(id) {
            window.location.href = `/payout/${id}`;
        }
    </script>
</x-app-layout>
