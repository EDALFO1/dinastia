<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dinastía ERP - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-900 text-white">
            <div class="p-6 border-b border-blue-800">
                <h1 class="text-2xl font-bold">Dinastía ERP</h1>
                <p class="text-blue-200 text-sm mt-1">Sistema de Gestión</p>
            </div>

            <!-- Empresa Selector -->
            <div class="p-4 border-b border-blue-800">
                <label class="text-blue-200 text-xs">Empresa Actual</label>
                <select class="w-full bg-blue-800 text-white p-2 rounded mt-2 text-sm">
                    <option>Test Company</option>
                </select>
            </div>

            <!-- Navigation -->
            <nav class="mt-6">
                <a href="/dashboard" class="flex items-center px-6 py-3 hover:bg-blue-800 transition {{ request()->routeIs('dashboard') ? 'bg-blue-800' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4v4"></path>
                    </svg>
                    Dashboard
                </a>

                <!-- Nómina -->
                <div class="px-6 py-2">
                    <h3 class="text-blue-300 text-xs font-semibold uppercase">Nómina</h3>
                </div>
                <a href="/afiliados" class="flex items-center px-6 py-3 hover:bg-blue-800 transition ml-4">
                    <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
                    Empleados
                </a>
                <a href="/recibos" class="flex items-center px-6 py-3 hover:bg-blue-800 transition ml-4">
                    <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
                    Recibos
                </a>
                <a href="/remisiones" class="flex items-center px-6 py-3 hover:bg-blue-800 transition ml-4">
                    <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
                    Remisiones
                </a>

                <!-- Facturación -->
                <div class="px-6 py-2 mt-4">
                    <h3 class="text-blue-300 text-xs font-semibold uppercase">Facturación</h3>
                </div>
                <a href="/invoices" class="flex items-center px-6 py-3 hover:bg-blue-800 transition ml-4">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>
                    Facturas
                </a>

                <!-- Contabilidad -->
                <div class="px-6 py-2 mt-4">
                    <h3 class="text-blue-300 text-xs font-semibold uppercase">Contabilidad</h3>
                </div>
                <a href="/journal-entries" class="flex items-center px-6 py-3 hover:bg-blue-800 transition ml-4">
                    <span class="w-2 h-2 bg-purple-400 rounded-full mr-3"></span>
                    Asientos
                </a>
                <a href="/reports/balance-sheet" class="flex items-center px-6 py-3 hover:bg-blue-800 transition ml-4">
                    <span class="w-2 h-2 bg-purple-400 rounded-full mr-3"></span>
                    Reportes
                </a>
            </nav>

            <!-- Bottom User -->
            <div class="absolute bottom-0 w-64 border-t border-blue-800 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">{{ auth()->user()->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-blue-300">{{ auth()->user()->email ?? 'test@test.com' }}</p>
                    </div>
                    <form action="/logout" method="POST">
                        @csrf
                        <button class="text-blue-300 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <div class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-8 py-4">
                    <h2 class="text-2xl font-bold text-gray-800">@yield('title')</h2>
                    <div class="flex items-center space-x-4">
                        <button class="text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-auto">
                <div class="p-8">
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
