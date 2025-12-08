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

        <button
            type="button"
            onclick="copyToClipboard('{{ url('/r?ref=' . $affiliate->ref_code) }}')"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 text-xs font-semibold hover:text-gray-700 hover:bg-gray-100"
        >
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
                            <select id="per_page" onchange="changePerPage(this.value)" class="text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
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
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Username, email, nomor..." class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Status Filter --}}
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="clicked" {{ request('status') == 'clicked' ? 'selected' : '' }}>Klik Link</option>
                                    <option value="joined_channel" {{ request('status') == 'joined_channel' ? 'selected' : '' }}>Join Chanel</option>
                                    <option value="purchased" {{ request('status') == 'purchased' ? 'selected' : '' }}>Sudah Beli</option>
                                </select>
                            </div>

                            {{-- Date From --}}
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Date To --}}
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="block w-full text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-search mr-2"></i>
                                Filter
                            </button>
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">
                                <i class="fas fa-redo mr-2"></i>
                                Reset
                            </a>
                            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
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
                                        <tr
                                            class="lead-row cursor-pointer hover:bg-gray-50"
                                            data-update-url="{{ route('leads.update', $lead) }}"
                                            data-username="{{ $lead->prospect_telegram_username ?? $lead->prospect_name }}"
                                            data-email="{{ $lead->prospect_email }}"
                                            data-phone="{{ $lead->prospect_phone }}"
                                            data-status="{{ $lead->status }}"
                                            data-notes="{{ $lead->notes }}"
                                        >
                                            <td class="px-3 py-2 border data">{{ $leads->firstItem() + $index }}</td>
                                            <td class="px-3 py-2 border">
                                                @if ($lead->created_at)
                                                    {{ $lead->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->prospect_telegram_username ?? $lead->prospect_name ?? '-' }}
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

                                            
                                            <td class="px-3 py-2 border truncate" title="@if ($lead->notes){{ $lead->notes }}@else{{ $lead->prospect_telegram_id ? 'Dari Telegram' : ($lead->prospect_email ? 'Dari Website' : '-') }}@endif">
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

    {{-- Modal Edit Prospek --}}
    <div id="lead-edit-modal" class="fixed inset-0 z-50 bg-black/40" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Prospek / Referral</h3>
                    <button type="button" id="lead-modal-close" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                {{-- Form --}}
                <form id="lead-edit-form" method="POST" action="#">
                    @csrf
                    @method('PATCH')

                    <div class="px-5 py-4 space-y-4">
                        {{-- Username (read-only) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username (Telegram / Nama)</label>
                            <p id="lead_username_display" class="mt-1 text-sm font-semibold text-gray-900">-</p>
                            <p class="text-xs text-gray-500">Username tidak bisa diubah dari sini.</p>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="lead_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="prospect_email" id="lead_email" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span id="lead_email_error" class="text-xs text-red-600 hidden"></span>
                        </div>

                        {{-- Nomor HP --}}
                        <div>
                            <label for="lead_phone" class="block text-sm font-medium text-gray-700">Nomor HP / WA</label>
                            <input type="number" name="prospect_phone" id="lead_phone" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span id="lead_phone_error" class="text-xs text-red-600 hidden"></span>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="lead_status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="lead_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="clicked">Klik Link</option>
                                <option value="joined_channel">Join Chanel</option>
                                <option value="purchased">Sudah Beli</option>
                            </select>
                        </div>                        {{-- Keterangan / Notes --}}
                        <div>
                            <label for="lead_notes" class="block text-sm font-medium text-gray-700">Keterangan</label>
                            <textarea name="notes" id="lead_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-lg">
                        <button type="button" id="lead-modal-cancel" class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">Batal</button>
                        <button type="submit" id="lead-submit-btn" class="px-4 py-2 text-sm rounded-md bg-slate-600 text-white hover:bg-slate-700">
                            <span id="submit-text">Simpan</span>
                            <span id="submit-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('lead-edit-modal');
        const form = document.getElementById('lead-edit-form');
        const submitBtn = document.getElementById('lead-submit-btn');
        const submitText = document.getElementById('submit-text');
        const submitLoading = document.getElementById('submit-loading');

        const usernameDisplay = document.getElementById('lead_username_display');
        const emailInput      = document.getElementById('lead_email');
        const phoneInput      = document.getElementById('lead_phone');
        const statusSelect    = document.getElementById('lead_status');
        const notesInput      = document.getElementById('lead_notes');

        const emailError = document.getElementById('lead_email_error');
        const phoneError = document.getElementById('lead_phone_error');

        function openModal() {
            modal.style.display = 'block';
            document.body.classList.add('overflow-hidden');
            // Reset errors
            clearErrors();
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.classList.remove('overflow-hidden');
            clearErrors();
        }

        function clearErrors() {
            emailError.textContent = '';
            emailError.classList.add('hidden');
            phoneError.textContent = '';
            phoneError.classList.add('hidden');
            emailInput.classList.remove('border-red-500');
            phoneInput.classList.remove('border-red-500');
        }

        function validateEmail(email) {
            if (!email) return true; // Email optional
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validatePhone(phone) {
            if (!phone) return true; // Phone optional
            const re = /^[0-9+\-\s()]+$/;
            return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
        }

        // Form validation
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            clearErrors();

            let isValid = true;
            const email = emailInput.value.trim();
            const phone = phoneInput.value.trim();

            // Validate email
            if (email && !validateEmail(email)) {
                emailError.textContent = 'Format email tidak valid';
                emailError.classList.remove('hidden');
                emailInput.classList.add('border-red-500');
                isValid = false;
            }

            // Validate phone
            if (phone && !validatePhone(phone)) {
                phoneError.textContent = 'Nomor telepon harus minimal 10 digit angka';
                phoneError.classList.remove('hidden');
                phoneInput.classList.add('border-red-500');
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Mohon periksa kembali input Anda',
                });
                return;
            }

            // Show loading
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');

            // Submit form
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data berhasil diupdate',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message || 'Gagal mengupdate data',
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            });
        });

        // klik row (double click untuk buka modal)
        document.querySelectorAll('.lead-row').forEach(row => {
            row.addEventListener('dblclick', () => {
                const updateUrl = row.dataset.updateUrl;
                const username  = row.dataset.username || '-';
                const email     = row.dataset.email || '';
                const phone     = row.dataset.phone || '';
                const status    = row.dataset.status || 'clicked';
                const notes     = row.dataset.notes || '';

                form.action           = updateUrl;
                usernameDisplay.textContent = username;
                emailInput.value      = email;
                phoneInput.value      = phone;
                statusSelect.value    = status;
                notesInput.value      = notes;

                openModal();
            });
            
            // Tambahkan cursor pointer dan hint
            row.style.cursor = 'pointer';
            row.title = 'Double click untuk edit';
        });

        document.getElementById('lead-modal-close').addEventListener('click', closeModal);
        document.getElementById('lead-modal-cancel').addEventListener('click', closeModal);

        // klik overlay di luar card untuk close
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    });
</script>

</x-app-layout>