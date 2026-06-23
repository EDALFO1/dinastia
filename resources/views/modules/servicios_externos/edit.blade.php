@extends('layouts.main')

@section('titulo', 'Editar Servicio')

@section('contenido')

    <div class="pagetitle">
        <h1>Editar Servicio</h1>
    </div>

        <div class="card">

            <div class="card-body">

                <h5 class="card-title">
                    Actualizar Servicio
                </h5>

                <form action="{{ route('servicios-externos.update', $serviciosExterno) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    @include('modules.servicios_externos.form')

                    <button class="btn btn-primary">
                        Actualizar
                    </button>

                    <a href="{{ route('servicios-externos.index') }}"
                       class="btn btn-secondary">

                        Volver

                    </a>

                </form>

            </div>

        </div>

@endsection