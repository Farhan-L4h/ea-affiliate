@section('title', 'Data Rekening Bank')

<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('affiliate.payout.index') }}" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                Data Rekening Bank
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
                            confirmButtonText: 'OK'
                        });
                    });
                </script>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('affiliate.payout.bank-info.update') }}">
                        @csrf
                        @method('PUT')

                        {{-- Bank Name --}}
                        <div class="mb-4">
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Bank <span class="text-red-500">*</span>
                            </label>
                            <select name="bank_name" 
                                    id="bank_name" 
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <option value="">Pilih Bank</option>
                                <option value="BCA" {{ old('bank_name', $affiliate->bank_name) == 'BCA' ? 'selected' : '' }}>BCA</option>
                                <option value="Mandiri" {{ old('bank_name', $affiliate->bank_name) == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                                <option value="BNI" {{ old('bank_name', $affiliate->bank_name) == 'BNI' ? 'selected' : '' }}>BNI</option>
                                <option value="BRI" {{ old('bank_name', $affiliate->bank_name) == 'BRI' ? 'selected' : '' }}>BRI</option>
                                <option value="CIMB Niaga" {{ old('bank_name', $affiliate->bank_name) == 'CIMB Niaga' ? 'selected' : '' }}>CIMB Niaga</option>
                                <option value="Permata" {{ old('bank_name', $affiliate->bank_name) == 'Permata' ? 'selected' : '' }}>Permata</option>
                                <option value="Danamon" {{ old('bank_name', $affiliate->bank_name) == 'Danamon' ? 'selected' : '' }}>Danamon</option>
                                <option value="BTN" {{ old('bank_name', $affiliate->bank_name) == 'BTN' ? 'selected' : '' }}>BTN</option>
                                <option value="BSI" {{ old('bank_name', $affiliate->bank_name) == 'BSI' ? 'selected' : '' }}>BSI (Bank Syariah Indonesia)</option>
                                <option value="Muamalat" {{ old('bank_name', $affiliate->bank_name) == 'Muamalat' ? 'selected' : '' }}>Muamalat</option>
                                <option value="OCBC NISP" {{ old('bank_name', $affiliate->bank_name) == 'OCBC NISP' ? 'selected' : '' }}>OCBC NISP</option>
                                <option value="Lainnya" {{ old('bank_name', $affiliate->bank_name) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('bank_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Account Holder Name --}}
                        <div class="mb-4">
                            <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Pemilik Rekening <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="account_holder_name" 
                                   id="account_holder_name" 
                                   value="{{ old('account_holder_name', $affiliate->account_holder_name) }}"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Sesuai dengan nama di rekening bank"
                                   required>
                            @error('account_holder_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Pastikan nama sesuai dengan yang tertera di rekening bank Anda
                            </p>
                        </div>

                        {{-- Account Number --}}
                        <div class="mb-6">
                            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Rekening <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="account_number" 
                                   id="account_number" 
                                   value="{{ old('account_number', $affiliate->account_number) }}"
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Contoh: 1234567890"
                                   required>
                            @error('account_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Info Box --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Informasi Penting</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Pastikan data yang Anda masukkan benar dan sesuai dengan rekening bank Anda</li>
                                            <li>Data ini akan digunakan untuk transfer komisi yang Anda ajukan</li>
                                            <li>Anda dapat mengubah data ini kapan saja sebelum mengajukan pencairan</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center justify-between">
                            <a href="{{ route('affiliate.payout.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Data Bank
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
