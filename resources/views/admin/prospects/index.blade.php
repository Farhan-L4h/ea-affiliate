@extends('layouts.admin')

@section('page-title', 'Manage Prospects')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h3 class="text-xl lg:text-2xl font-bold">Data Prospek / Leads</h3>
        <p class="text-xs lg:text-sm text-gray-600">Kelola semua prospek dari semua affiliate</p>
    </div>

    {{-- Filter Form --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Username, email, phone..." class="w-full rounded-md border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">Semua Status</option>
                    <option value="clicked" {{ request('status') === 'clicked' ? 'selected' : '' }}>Klik Link</option>
                    <option value="joined_channel" {{ request('status') === 'joined_channel' ? 'selected' : '' }}>Join Channel</option>
                    <option value="purchased" {{ request('status') === 'purchased' ? 'selected' : '' }}>Sudah Beli</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Affiliate</label>
                <select name="affiliate" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">Semua Affiliate</option>
                    @foreach($affiliates as $aff)
                    <option value="{{ $aff->ref_code }}" {{ request('affiliate') === $aff->ref_code ? 'selected' : '' }}>
                        {{ $aff->user->name }} ({{ $aff->ref_code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 text-sm">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mt-4">
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <a href="{{ route('admin.prospects.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs lg:text-sm text-gray-600">Per Page:</label>
                <select name="per_page" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-3 py-3 text-left">#</th>
                    <th class="px-3 py-3 text-left">Tanggal</th>
                    <th class="px-3 py-3 text-left">Username</th>
                    <th class="px-3 py-3 text-left">Email</th>
                    <th class="px-3 py-3 text-left">Phone</th>
                    <th class="px-3 py-3 text-left">Status</th>
                    <th class="px-3 py-3 text-left">Affiliate</th>
                    <th class="px-3 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prospects as $index => $prospect)
                <tr class="border-b hover:bg-gray-50 cursor-pointer"
                    ondblclick="window.location='{{ route('admin.prospects.show', $prospect) }}'"
                    title="Double click untuk melihat detail">
                    <td class="px-3 py-3">{{ $prospects->firstItem() + $index }}</td>
                    <td class="px-3 py-3 text-xs">
                        {{ $prospect->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-3 py-3">{{ $prospect->prospect_telegram_username ?? $prospect->prospect_name ?? '-' }}</td>
                    <td class="px-3 py-3 truncate">{{ $prospect->prospect_email ?? '-' }}</td>
                    <td class="px-3 py-3">{{ $prospect->prospect_phone ?? '-' }}</td>
                    <td class="px-3 py-3">
                        @if($prospect->status === 'clicked')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Klik Link</span>
                        @elseif($prospect->status === 'joined_channel')
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Join Channel</span>
                        @else
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">Sudah Beli</span>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                            {{ $prospect->affiliate->user->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <a href="{{ route('admin.prospects.show', $prospect) }}" class="text-blue-600 hover:text-blue-800" title="Detail" onclick="event.stopPropagation()">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada data prospek
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $prospects->links() }}
    </div>
</div>
@endsection
