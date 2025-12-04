<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Affiliate Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Referral Info --}}
                    <div class="mb-4">
                        <p><strong>Referral Code:</strong> {{ $affiliate->ref_code }}</p>
                        
                        <p class="text-sm text-gray-500 mt-1">
                            Link Telegram langsung:
                            https://t.me/{{ config('services.telegram.username') }}?start={{ $affiliate->ref_code }}
                        </p>
                        <a href="https://t.me/{{ config('services.telegram.username') }}?start={{ $affiliate->ref_code }}" target="_blank" class="inline-flex items-center px-3 py-2 mt-2 rounded-md bg-emerald-600 text-white text-xs font-semibold">Copy</a>
                    </div>


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
                    <h2 class="text-lg font-semibold mt-8 mb-2">Data Prospek / Referral</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border table-auto table-striped">
                                <thead>
                                    <tr class="bg-gray-100 ">
                                        <th class="px-3 py-2 border text-start">Tanggal</th>
                                        <th class="px-3 py-2 border text-start">Username</th>
                                        <th class="px-3 py-2 border text-start">Email</th>
                                        <th class="px-3 py-2 border text-start">Nomor</th>
                                        <th class="px-3 py-2 border text-start">Status</th>
                                        <th class="px-3 py-2 border text-start">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($leads as $lead)
                                        <tr
                                            class="lead-row cursor-pointer hover:bg-gray-50"
                                            data-update-url="{{ route('leads.update', $lead) }}"
                                            data-username="{{ $lead->prospect_telegram_username ?? $lead->prospect_name }}"
                                            data-email="{{ $lead->prospect_email }}"
                                            data-phone="{{ $lead->prospect_phone }}"
                                            data-status="{{ $lead->status }}"
                                            data-notes="{{ $lead->notes }}"
                                        >
                                            <td class="px-3 py-2 border">
                                                {{ $lead->created_at?->format('d-m-Y H:i') }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->prospect_telegram_username ?? $lead->prospect_name ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->prospect_email ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                {{ $lead->prospect_phone ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border">
                                                @switch($lead->status)
                                                    @case('joined_bot')
                                                        Join Grub
                                                        @break
                                                    @case('purchased')
                                                        Sudah Beli
                                                        @break
                                                    @default
                                                        Klik Link
                                                @endswitch
                                            </td>
                                            <td class="px-3 py-2 border">
                                                @if ($lead->notes)
                                                    {{ $lead->notes }}
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
                                            <td colspan="6" class="px-3 py-2 border text-center text-gray-500">
                                                Belum ada prospek.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
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
                        </div>

                        {{-- Nomor HP --}}
                        <div>
                            <label for="lead_phone" class="block text-sm font-medium text-gray-700">Nomor HP / WA</label>
                            <input type="text" name="prospect_phone" id="lead_phone" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="lead_status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="lead_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="clicked">Klik Link</option>
                                <option value="joined_bot">Join Grub</option>
                                <option value="purchased">Sudah Beli</option>
                            </select>
                        </div>

                        {{-- Keterangan / Notes --}}
                        <div>
                            <label for="lead_notes" class="block text-sm font-medium text-gray-700">Keterangan</label>
                            <textarea name="notes" id="lead_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 px-5 py-3 border-t bg-gray-50 rounded-b-lg">
                        <button type="button" id="lead-modal-cancel" class="px-4 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm rounded-md bg-slate-600 text-white hover:bg-slate-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('lead-edit-modal');
        const form = document.getElementById('lead-edit-form');

        const usernameDisplay = document.getElementById('lead_username_display');
        const emailInput      = document.getElementById('lead_email');
        const phoneInput      = document.getElementById('lead_phone');
        const statusSelect    = document.getElementById('lead_status');
        const notesInput      = document.getElementById('lead_notes');

        function openModal() {
            modal.style.display = 'block';
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.classList.remove('overflow-hidden');
        }

        // klik row
        document.querySelectorAll('.lead-row').forEach(row => {
            row.addEventListener('click', () => {
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