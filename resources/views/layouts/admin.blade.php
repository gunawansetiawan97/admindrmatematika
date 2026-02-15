<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Kelas Olimpiade')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('partials.katex')
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-gray-800 text-white shadow-lg" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Desktop Menu -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold">Admin Panel</a>
                    <div class="hidden md:flex space-x-1">
                        <a href="{{ route('admin.dashboard') }}" class="hover:bg-gray-700 px-3 py-2 rounded">Dashboard</a>
                        <a href="{{ route('admin.packages.index') }}" class="hover:bg-gray-700 px-3 py-2 rounded">Paket Soal</a>
                        <a href="{{ route('admin.students.index') }}" class="hover:bg-gray-700 px-3 py-2 rounded">Murid</a>
                        <a href="{{ route('admin.orders.index') }}" class="hover:bg-gray-700 px-3 py-2 rounded relative">
                            Pesanan
                            @php
                                $pendingPayments = \App\Models\Payment::where('status', 'pending')->count();
                            @endphp
                            @if($pendingPayments > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ $pendingPayments }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.subscriptions.index') }}" class="hover:bg-gray-700 px-3 py-2 rounded">Kelas</a>
                        <a href="{{ route('admin.classrooms.index') }}" class="hover:bg-gray-700 px-3 py-2 rounded">Kelas</a>
                    </div>
                </div>

                <!-- Right Side: Profile & Logout (Desktop) -->
                <div class="hidden md:flex items-center space-x-4">
                    <span>{{ Auth::guard('admin')->user()->name }}</span>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="hover:bg-gray-700 px-3 py-2 rounded">Logout</button>
                    </form>
                </div>

                <!-- Mobile: Hamburger -->
                <div class="flex md:hidden items-center">
                    <!-- Hamburger Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="hover:bg-gray-700 p-2 rounded focus:outline-none">
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-gray-700">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block hover:bg-gray-600 px-3 py-2 rounded">Dashboard</a>
                <a href="{{ route('admin.packages.index') }}" class="block hover:bg-gray-600 px-3 py-2 rounded">Paket Soal</a>
                <a href="{{ route('admin.students.index') }}" class="block hover:bg-gray-600 px-3 py-2 rounded">Murid</a>
                <a href="{{ route('admin.orders.index') }}" class="block hover:bg-gray-600 px-3 py-2 rounded flex items-center justify-between">
                    <span>Pesanan</span>
                    @if($pendingPayments > 0)
                        <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ $pendingPayments }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="block hover:bg-gray-600 px-3 py-2 rounded">Kelas</a>
                <a href="{{ route('admin.classrooms.index') }}" class="block hover:bg-gray-600 px-3 py-2 rounded">Kelas</a>
                <div class="border-t border-gray-500 my-2"></div>
                <div class="px-3 py-2 text-gray-300 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ Auth::guard('admin')->user()->name }}
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left hover:bg-gray-600 px-3 py-2 rounded flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('scripts')
</body>
</html>
