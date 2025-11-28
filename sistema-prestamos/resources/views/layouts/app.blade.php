<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 flex">

        <aside class="w-64 bg-white shadow-md">
            <div class="p-4 font-bold text-lg border-b">
                Panel
            </div>

            <ul class="mt-4 space-y-1">
                <li class="nav-item">
                    <a class="nav-link block px-4 py-2 hover:bg-gray-200 {{ request()->routeIs('dashboard') ? 'bg-gray-300' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link block px-4 py-2 hover:bg-gray-200 {{ request()->routeIs('estudiantes.*') ? 'bg-gray-300' : '' }}"
                       href="{{ route('estudiantes.index') }}">
                        <i class="fas fa-users"></i> Estudiantes
                    </a>
                </li>
            </ul>
        </aside>

        <div class="flex-1">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="p-6">
                @isset($slot)
                    {{ $slot }}
                @elseif(View::hasSection('content'))
                    @yield('content')
                @endif
            </main>
        </div>

    </div>
</body>
</html>
