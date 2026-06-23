@extends('layouts.main')

@section('titulo', 'Nuevo Servicio')

@section('contenido')

    <div class="pagetitle">
        <h1>Nuevo Servicio</h1>
    </div>

        <div class="card">

            <div class="card-body">

                <h5 class="card-title">
                    Registrar Servicio
                </h5>

                <form action="{{ route('servicios-externos.store') }}"
                      method="POST">

                    @csrf

                    @include('modules.servicios_externos.form')

                    <button class="btn btn-success">
                        Guardar
                    </button>

                    <a href="{{ route('servicios-externos.index') }}"
                       class="btn btn-secondary">

                        Volver

                    </a>

                </form>

            </div>

        </div>

@endsection