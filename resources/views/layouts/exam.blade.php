<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ujian - Kelas Olimpiade')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('partials.katex')
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <span class="text-xl font-bold">@yield('exam-title', 'Ujian')</span>
                </div>
                <div class="flex items-center space-x-4">
                    @hasSection('timer')
                        <div id="timer" class="bg-blue-700 px-4 py-2 rounded font-mono text-lg">
                            @yield('timer')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-20 pb-6 px-4">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
