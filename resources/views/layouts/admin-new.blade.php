<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white flex flex-col">
            <div class="p-4 text-xl font-bold border-b border-gray-700">
                <i class="fas fa-shield-alt mr-2"></i>
                Admin Panel
            </div>
            
            <nav class="flex-1 overflow-y-auto">
                <ul class="p-4 space-y-2">
                    <li>
                        <a href="{{ url('/admin/dashboard') }}" 
                           class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('admin/dashboard') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-tachometer-alt w-6"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/affiliates') }}" 
                           class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('admin/affiliates*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-users w-6"></i>
                            Affiliates
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/prospects') }}" 
                           class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('admin/prospects*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-user-plus w-6"></i>
                            Prospek / Leads
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/orders') }}" 
                           class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('admin/orders*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-shopping-cart w-6"></i>
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/sales') }}" 
                           class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('admin/sales*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-dollar-sign w-6"></i>
                            Sales
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.payouts.index') }}" 
                           class="flex items-center p-2 rounded hover:bg-gray-700 {{ request()->is('admin/payouts*') ? 'bg-gray-700' : '' }}">
                            <i class="fas fa-money-bill-wave w-6"></i>
                            Pencairan Komisi
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="p-4 border-t border-gray-700">
                <div class="mb-2 text-sm text-gray-400">Logged in as:</div>
                <div class="font-medium">{{ Auth::user()->name ?? 'Administrator' }}</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif
</body>
</html>
