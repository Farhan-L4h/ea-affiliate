<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title', 'Dashboard') - Admin Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-btn" class="fixed top-4 left-4 z-[60] lg:hidden bg-gray-800 text-white p-3 rounded-lg shadow-lg">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <!-- Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

        <!-- Sidebar -->
        <div id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 bg-gray-900 px-4">
                <h1 class="text-xl font-bold">
                    <i class="fas fa-shield-alt mr-2"></i>Admin Panel
                </h1>
                <button id="close-sidebar" class="lg:hidden text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-chart-line w-5 mr-3"></i>
                    Dashboard
                </a>

                <a href="{{ route('admin.affiliates.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.affiliates.*') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-users w-5 mr-3"></i>
                    Affiliates
                </a>

                <a href="{{ route('admin.prospects.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.prospects.*') ? 'bg-gray-700 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-user-friends w-5 mr-3"></i>
                    Prospek / Leads
                </a>

                <div class="border-t border-gray-700 my-4"></div>

                {{-- <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 hover:bg-gray-700">
                    <i class="fas fa-arrow-left w-5 mr-3"></i>
                    Ke Dashboard Affiliate
                </a> --}}
            </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm h-16">
                <div class="flex items-center justify-between h-full px-4 lg:px-6">
                    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 ml-14 lg:ml-0">
                        @yield('page-title', 'Dashboard')
                    </h2>

                    <div class="flex items-center gap-2 lg:gap-4">
                        <span class="text-xs lg:text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs lg:text-sm text-red-600 hover:text-red-700">
                                <i class="fas fa-sign-out-alt mr-1"></i><span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Global SweetAlert for session messages --}}
    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    </script>
    @endif

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            showConfirmButton: true
        });
    </script>
    @endif

    @stack('scripts')

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const closeSidebar = document.getElementById('close-sidebar');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
            mobileMenuBtn.classList.add('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebarFunc() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            mobileMenuBtn.classList.remove('hidden');
            document.body.style.overflow = '';
        }

        mobileMenuBtn?.addEventListener('click', openSidebar);
        closeSidebar?.addEventListener('click', closeSidebarFunc);
        sidebarOverlay?.addEventListener('click', closeSidebarFunc);

        // Close sidebar when clicking any navigation link on mobile
        if (window.innerWidth < 1024) {
            document.querySelectorAll('#sidebar a').forEach(link => {
                link.addEventListener('click', closeSidebarFunc);
            });
        }
    </script>
</body>
</html>
