@extends('layouts.admin')

@section('page-title', 'Tambah Affiliate')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold mb-6">Tambah Affiliate Baru</h3>

        <form action="{{ route('admin.affiliates.store') }}" method="POST">
            @csrf

            <div class="space-y-4">
                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required class="w-full rounded-md border-gray-300 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-md border-gray-300 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <input type="password" name="password" id="password" required class="w-full rounded-md border-gray-300 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Is Active --}}
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" checked class="rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Aktifkan akun</label>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
                <a href="{{ route('admin.affiliates.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
@push('scripts')
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endpush
@endif
@endsection
