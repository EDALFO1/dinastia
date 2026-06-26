@extends('layouts.app')

@section('title', 'Facturas')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Facturas</h3>
    <a href="/invoices/create" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
        + Nueva Factura
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
            <input type="text" placeholder="Ej: INV-00001" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
            <input type="text" placeholder="Buscar..." class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
            <input type="date" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estado DIAN</label>
            <select class="w-full border rounded-lg px-3 py-2 text-sm">
                <option>Todos</option>
                <option>Aceptada</option>
                <option>Pendiente</option>
                <option>Rechazada</option>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Local</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado DIAN</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">FV-0001</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Empresa ABC S.A.S</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2026-06-20</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$5,000,000</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Borrador</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pendiente</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                    <a href="#" class="text-green-600 hover:text-green-900">Firmar</a>
                    <a href="#" class="text-purple-600 hover:text-purple-900">DIAN</a>
                </td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">FV-0002</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Empresa XYZ Ltda</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2026-06-18</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$3,500,000</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Enviada</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Aceptada</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                    <a href="#" class="text-red-600 hover:text-red-900">PDF</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
