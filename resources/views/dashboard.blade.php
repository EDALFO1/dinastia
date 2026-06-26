@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Empleados -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Empleados</p>
                <p class="text-3xl font-bold text-gray-800">0</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
        </div>
        <a href="/afiliados" class="mt-4 text-blue-600 text-sm hover:text-blue-800">Ver empleados →</a>
    </div>

    <!-- Recibos -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Recibos de Nómina</p>
                <p class="text-3xl font-bold text-gray-800">0</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
        <a href="/recibos" class="mt-4 text-blue-600 text-sm hover:text-blue-800">Ver recibos →</a>
    </div>

    <!-- Facturas -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Facturas</p>
                <p class="text-3xl font-bold text-gray-800">0</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-lg">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <a href="/invoices" class="mt-4 text-blue-600 text-sm hover:text-blue-800">Ver facturas →</a>
    </div>

    <!-- Asientos -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Asientos Contables</p>
                <p class="text-3xl font-bold text-gray-800">0</p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
            </div>
        </div>
        <a href="/journal-entries" class="mt-4 text-blue-600 text-sm hover:text-blue-800">Ver asientos →</a>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones Rápidas</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="/afiliados/create" class="flex items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
            <div class="text-center">
                <svg class="w-6 h-6 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <p class="text-sm font-medium text-gray-700">Nuevo Empleado</p>
            </div>
        </a>

        <a href="/recibos/create" class="flex items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition">
            <div class="text-center">
                <svg class="w-6 h-6 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <p class="text-sm font-medium text-gray-700">Nuevo Recibo</p>
            </div>
        </a>

        <a href="/invoices/create" class="flex items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
            <div class="text-center">
                <svg class="w-6 h-6 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <p class="text-sm font-medium text-gray-700">Nueva Factura</p>
            </div>
        </a>

        <a href="/journal-entries/create" class="flex items-center justify-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition">
            <div class="text-center">
                <svg class="w-6 h-6 text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <p class="text-sm font-medium text-gray-700">Nuevo Asiento</p>
            </div>
        </a>
    </div>
</div>

<!-- System Status -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Estado del Sistema</h3>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-gray-600">Base de Datos</span>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">✓ OK</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-gray-600">API REST</span>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">✓ OK</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-gray-600">DIAN Integration</span>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">✓ Ready</span>
        </div>
    </div>
</div>
@endsection
