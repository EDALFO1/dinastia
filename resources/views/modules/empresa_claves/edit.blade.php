@extends('layouts.main')

@section('titulo', 'Editar Clave')

@section('contenido')

    <div class="pagetitle">
        <h1>Editar Clave</h1>
    </div>

        <div class="card">

            <div class="card-body">

                <h5 class="card-title">
                    Actualizar Información
                </h5>

                <form action="{{ route('empresa-claves.update', $empresaClave) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    @include('modules.empresa_claves.form')

                    <button type="submit"
                            class="btn btn-primary">

                        Actualizar

                    </button>

                    <a href="{{ route('empresa-claves.index') }}"
                       class="btn btn-secondary">

                        Volver

                    </a>

                </form>

            </div>

        </div>

@endsection

