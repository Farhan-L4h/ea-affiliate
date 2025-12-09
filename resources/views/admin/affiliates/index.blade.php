@extends('layouts.admin')

@section('page-title', 'Manage Affiliates')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl lg:text-2xl font-bold">Affiliates</h3>
            <p class="text-xs lg:text-sm text-gray-600">Kelola semua affiliate</p>
        </div>
        <a href="{{ route('admin.affiliates.create') }}" class="w-full sm:w-auto text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Tambah Affiliate
        </a>
    </div>

    {{-- Filter Form --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, phone, email..." class="w-full rounded-md border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                <select name="per_page" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                    <i class="fas fa-search mr-1"></i>Filter
                </button>
                <a href="{{ route('admin.affiliates.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                    <i class="fas fa-redo mr-1"></i>Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Ref Code</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Prospek</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($affiliates as $index => $affiliate)
                <tr class="border-b hover:bg-gray-50 cursor-pointer" 
                    ondblclick="window.location='{{ route('admin.affiliates.show', $affiliate) }}'"
                    title="Double click untuk melihat detail">
                    <td class="px-4 py-3">{{ $affiliates->firstItem() + $index }}</td>
                    <td class="px-4 py-3 font-medium">{{ $affiliate->name }}</td>
                    <td class="px-4 py-3">{{ $affiliate->phone }}</td>
                    <td class="px-4 py-3">{{ $affiliate->email ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($affiliate->affiliate)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                            {{ $affiliate->affiliate->ref_code }}
                        </span>
                        @else
                        -
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($affiliate->is_active)
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">
                            <i class="fas fa-check-circle mr-1"></i>Aktif
                        </span>
                        @else
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">
                            <i class="fas fa-times-circle mr-1"></i>Nonaktif
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($affiliate->affiliate)
                        {{ $affiliate->affiliate->referralTracks->count() }}
                        @else
                        0
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.affiliates.show', $affiliate) }}" class="text-blue-600 hover:text-blue-800" title="Detail" onclick="event.stopPropagation()">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada data affiliate
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $affiliates->links() }}
    </div>
</div>
@endsection
