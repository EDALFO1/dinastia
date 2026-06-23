@extends('layouts.main')

@section('titulo', 'Nueva Clave')

@section('contenido')

    <div class="pagetitle">
        <h1>Nueva Clave</h1>
    </div>

        <div class="card">

            <div class="card-body">

                <h5 class="card-title">
                    Registrar Clave
                </h5>

                <form action="{{ route('empresa-claves.store') }}"
                      method="POST">

                    @csrf

                    @include('modules.empresa_claves.form')

                    <button type="submit"
                            class="btn btn-success">

                        Guardar

                    </button>

                    <a href="{{ route('empresa-claves.index') }}"
                       class="btn btn-secondary">

                        Volver

                    </a>

                </form>

            </div>

        </div>

@endsection
