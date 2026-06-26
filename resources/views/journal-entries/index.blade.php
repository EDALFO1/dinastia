@extends('layouts.app')

@section('title', 'Asientos Contables')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Asientos Contables</h3>
    <a href="/journal-entries/create" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
        + Nuevo Asiento
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Asiento #</label>
            <input type="text" placeholder="Ej: 202206-001" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
            <input type="date" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Concepto</label>
            <input type="text" placeholder="Buscar..." class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
            <select class="w-full border rounded-lg px-3 py-2 text-sm">
                <option>Todos</option>
                <option>Borrador</option>
                <option>Posteado</option>
                <option>Rechazado</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                Filtrar
            </button>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asiento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Débito</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crédito</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">202206-001</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2026-06-20</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Venta de servicios</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$10,000,000</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$10,000,000</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Posteado</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                    <a href="#" class="text-red-600 hover:text-red-900">Detalles</a>
                </td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">202206-002</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2026-06-19</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Pago de nómina</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$5,800,000</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$5,800,000</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Borrador</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                    <a href="#" class="text-green-600 hover:text-green-900">Aprobar</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Summary -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-gray-600 text-sm">Total Débitos (Junio)</p>
        <p class="text-2xl font-bold text-gray-800">$15,800,000</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-gray-600 text-sm">Total Créditos (Junio)</p>
        <p class="text-2xl font-bold text-gray-800">$15,800,000</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-gray-600 text-sm">Saldo</p>
        <p class="text-2xl font-bold text-green-600">$0</p>
    </div>
</div>
@endsection
