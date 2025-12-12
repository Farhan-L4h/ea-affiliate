<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembayaran - {{ $order->order_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">
                    💳 Detail Pembayaran
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Order ID: <span class="font-mono font-semibold">{{ $order->order_id }}</span>
                </p>
            </div>

            <!-- Status -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                @if($order->status === 'paid')
                    <div class="bg-green-50 border-l-4 border-green-500 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    ✅ Pembayaran Berhasil!
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif($order->status === 'expired')
                    <div class="bg-red-50 border-l-4 border-red-500 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    ❌ Pembayaran Kadaluarsa
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-800">
                                    ⏳ Menunggu Pembayaran
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Order Details -->
                <div class="p-6 space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">📦 Detail Pesanan</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Produk:</span>
                                <span class="font-semibold">{{ $order->product }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Harga:</span>
                                <span>Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kode Unik:</span>
                                <span class="font-mono font-semibold text-blue-600">{{ $order->unique_code }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-900 font-semibold">Total Transfer:</span>
                                <span class="text-xl font-bold text-green-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($order->status === 'pending')
                        <!-- Payment Info -->
                        <div class="border-t pt-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">💳 Informasi Transfer</h3>
                            <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bank:</span>
                                    <span class="font-semibold">{{ $order->payment_info['bank'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">No. Rekening:</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-semibold" id="account-number">{{ $order->payment_info['account_number'] }}</span>
                                        <button onclick="copyToClipboard('{{ $order->payment_info['account_number'] }}')" class="text-blue-600 hover:text-blue-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Atas Nama:</span>
                                    <span class="font-semibold">{{ $order->payment_info['account_name'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Timer -->
                        <div class="border-t pt-4">
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <p class="text-sm text-gray-600 mb-2">⏰ Batas Waktu Pembayaran</p>
                                <p class="text-2xl font-bold text-blue-600" id="countdown">
                                    {{ $order->expired_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="border-t pt-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">📝 Cara Pembayaran</h3>
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
                                <li>Transfer sesuai nominal <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></li>
                                <li>Pastikan nominal transfer <strong>termasuk kode unik ({{ $order->unique_code }})</strong></li>
                                <li>Transfer ke rekening yang tertera di atas</li>
                                <li>Pembayaran akan otomatis terverifikasi dalam 1-5 menit</li>
                                <li>Link download akan dikirim ke Telegram setelah pembayaran berhasil</li>
                            </ol>
                        </div>
                    @elseif($order->status === 'paid')
                        <div class="border-t pt-4">
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <p class="text-sm text-gray-600 mb-2">Dibayar pada</p>
                                <p class="text-lg font-semibold text-green-600">
                                    {{ $order->paid_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-600">
                <p>Butuh bantuan? Hubungi admin kami</p>
                <p class="mt-2">📱 <a href="https://t.me/admin" class="text-blue-600 hover:underline">Telegram Support</a></p>
            </div>
        </div>
    </div>

    <script>
        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Nomor rekening berhasil disalin!');
            });
        }

        // Auto refresh status every 10 seconds if pending
        @if($order->status === 'pending')
            setInterval(() => {
                fetch('{{ route("payment.check-status", $order->order_id) }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.data.status === 'paid') {
                            location.reload();
                        }
                    });
            }, 10000);

            // Countdown timer
            const expiredAt = new Date('{{ $order->expired_at->toIso8601String() }}');
            const countdownEl = document.getElementById('countdown');

            function updateCountdown() {
                const now = new Date();
                const diff = expiredAt - now;

                if (diff <= 0) {
                    countdownEl.textContent = 'Kadaluarsa';
                    countdownEl.classList.add('text-red-600');
                    location.reload();
                    return;
                }

                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                countdownEl.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        @endif
    </script>
</body>
</html>
