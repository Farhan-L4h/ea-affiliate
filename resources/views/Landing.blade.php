<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>EA HabaGridPro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-3xl w-full bg-white shadow-md rounded-lg p-8">

            {{-- alert sukses --}}
            @if (session('success'))
                <div class="mb-4 rounded border border-green-400 bg-green-100 px-4 py-2 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <h1 class="text-2xl font-bold mb-2">EA HabaGridPro</h1>
            <p class="text-gray-600 mb-4">
                EA grid dengan manajemen risiko, cocok buat trading semi-otomatis.
            </p>

            <div class="mb-6">
                <p class="text-lg font-semibold">Harga: <span class="text-emerald-600">Rp 1.000.000</span></p>
                <p class="text-xs text-gray-500">*harga dummy untuk testing sistem affiliate</p>
            </div>

            {{-- Form Checkout --}}
            <form action="{{ route('checkout.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="name">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        required
                    >
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"
                >
                    Beli Sekarang (Dummy Checkout)
                </button>
            </form>

            <hr class="my-6">

            <p class="text-xs text-gray-500">
                Jika kamu datang dari link affiliate, sistem sudah otomatis menyimpan referral-nya.
            </p>
        </div>
    </div>
</body>
</html>
