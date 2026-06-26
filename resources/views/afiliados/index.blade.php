@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-lg font-semibold">Listado de Empleados</h3>
    <a href="/afiliados/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        + Nuevo Empleado
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Juan García López</p>
                        <p class="text-xs text-gray-500">Cargo: Gerente</p>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1234567890</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">juan@example.com</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">310-555-1234</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Activo</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                    <a href="#" class="text-yellow-600 hover:text-yellow-900">Editar</a>
                    <a href="#" class="text-red-600 hover:text-red-900">Eliminar</a>
                </td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                        <p class="text-sm font-medium text-gray-900">María Rodríguez Pérez</p>
                        <p class="text-xs text-gray-500">Cargo: Contador</p>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">9876543210</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">maria@example.com</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">310-555-5678</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Activo</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                    <a href="#" class="text-yellow-600 hover:text-yellow-900">Editar</a>
                    <a href="#" class="text-red-600 hover:text-red-900">Eliminar</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Pagination Info -->
<div class="mt-4 flex justify-between items-center">
    <p class="text-sm text-gray-600">Mostrando 2 de 2 empleados</p>
    <div class="space-x-2">
        <button class="px-4 py-2 border rounded text-sm text-gray-600 cursor-not-allowed opacity-50">Anterior</button>
        <button class="px-4 py-2 border rounded text-sm text-gray-600 cursor-not-allowed opacity-50">Siguiente</button>
    </div>
</div>
@endsection
