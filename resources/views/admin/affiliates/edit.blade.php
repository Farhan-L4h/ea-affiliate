@extends('layouts.admin')

@section('page-title', 'Edit Affiliate')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold mb-6">Edit Affiliate</h3>

        <form action="{{ route('admin.affiliates.update', $affiliate) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $affiliate->name) }}" required class="w-full rounded-md border-gray-300 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                    <input type="number" name="phone" id="phone" value="{{ old('phone', $affiliate->phone) }}" required class="w-full rounded-md border-gray-300 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $affiliate->email) }}" class="w-full rounded-md border-gray-300 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="w-full rounded-md border-gray-300 @error('password') border-red-500 @enderror pr-10">
                        <button type="button" onclick="togglePassword()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="toggle-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Is Active --}}
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" {{ old('is_active', $affiliate->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Aktifkan akun</label>
                </div>

                {{-- Ref Code (read-only) --}}
                @if($affiliate->affiliate)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referral Code</label>
                    <input type="text" value="{{ $affiliate->affiliate->ref_code }}" disabled class="w-full rounded-md border-gray-300 bg-gray-100">
                </div>
                @endif
            </div>

            <div class="flex items-center gap-2 mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
                <a href="{{ route('admin.affiliates.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endpush
@endsection
