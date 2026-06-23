@extends('layouts.main')

@section('titulo', 'Servicios Externos')

@section('contenido')

    <div class="pagetitle">
        <h1>Servicios Externos</h1>
    </div>

        <div class="card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3 mt-3">

                    <h5 class="card-title mb-0">
                        Listado de Servicios
                    </h5>

                    <a href="{{ route('servicios-externos.create') }}"
                       class="btn btn-primary">

                        <i class="bi bi-plus-circle"></i>
                        Nuevo Servicio

                    </a>

                </div>

                {{-- MENSAJES --}}
                @if(session('success'))

                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>

                @endif

                <div class="table-responsive">

                    <table class="table table-striped align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Nombre</th>
                                <th>URL</th>
                                <th>Estado</th>
                                <th width="140">Acciones</th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($servicios as $servicio)

                                <tr>

                                    <td>
                                        {{ $servicio->nombre }}
                                    </td>

                                    <td>

                                        @if($servicio->url)

                                            <a href="{{ $servicio->url }}"
                                               target="_blank">

                                                {{ $servicio->url }}

                                            </a>

                                        @else
                                            —
                                        @endif

                                    </td>

                                    <td>

                                        @if($servicio->activo)

                                            <span class="badge bg-success">
                                                Activo
                                            </span>

                                        @else

                                            <span class="badge bg-danger">
                                                Inactivo
                                            </span>

                                        @endif

                                    </td>

                                    <td>

                                        <div class="d-flex gap-2">

                                            <a href="{{ route('servicios-externos.edit', $servicio) }}"
                                               class="btn btn-warning btn-sm">

                                                <i class="bi bi-pencil-square"></i>

                                            </a>

                                            <form action="{{ route('servicios-externos.destroy', $servicio) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar servicio?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="4"
                                        class="text-center text-muted">

                                        No hay servicios registrados.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">
                    {{ $servicios->links() }}
                </div>

            </div>

        </div>

@endsection