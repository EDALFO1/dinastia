@extends('layouts.app')

@section('title', 'Recibos de Nómina')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Recibos de Nómina</h3>
    <a href="/recibos/create" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
        + Nuevo Recibo
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Empleado</label>
            <input type="text" placeholder="Buscar..." class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Periodo</label>
            <select class="w-full border rounded-lg px-3 py-2 text-sm">
                <option>Junio 2026</option>
                <option>Mayo 2026</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
            <select class="w-full border rounded-lg px-3 py-2 text-sm">
                <option>Todos</option>
                <option>Pagado</option>
                <option>Pendiente</option>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibo #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empleado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devengado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descuentos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Neto</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">REC-0001</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Juan García López</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Junio 2026</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$3,500,000</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$800,000</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$2,700,000</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Enviado</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                    <a href="#" class="text-red-600 hover:text-red-900">PDF</a>
                </td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">REC-0002</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">María Rodríguez Pérez</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Junio 2026</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$4,000,000</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$900,000</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$3,100,000</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Pagado</span>
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
