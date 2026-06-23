@php
use Illuminate\Support\Facades\Crypt;
@endphp

@extends('layouts.main')

@section('titulo', 'Claves por Empresa')

@section('contenido')

<style>

    /* CONTENEDOR GENERAL */
    .claves-wrapper{
    padding-right: 10px;
}

    /* CARD PRINCIPAL */
    .claves-card{
        border: none;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0,0,0,.05);
    }

    /* TITULO */
    .claves-title{
        font-size: 30px;
        font-weight: 700;
        color: #0b3b8c;
        margin-bottom: 10px;
    }

    /* SUBTITULO */
    .claves-subtitle{
        font-size: 26px;
        font-weight: 600;
        color: #0b3b8c;
    }

    /* TABLA */
    .table-claves thead th{
        background: #f8fafc;
        border-bottom: 2px solid #e5e7eb;
        color: #374151;
        font-weight: 700;
        font-size: 14px;
        padding: 14px;
        white-space: nowrap;
    }

    .table-claves tbody td{
        vertical-align: middle;
        padding: 14px;
        font-size: 14px;
    }

    .table-claves tbody tr:hover{
        background: #f8fbff;
    }

    /* BADGE SERVICIO */
    .servicio-badge{
        background: linear-gradient(90deg,#2563eb,#1d4ed8);
        color: white;
        padding: 7px 14px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        display: inline-block;
    }

    /* PASSWORD WRAPPER */
    .pw-wrapper{
        display: flex;
        align-items: center;
        gap: 5px;
        max-width: 260px;
    }

    .password-group{
        flex: 1;
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        transition: border-color .2s, box-shadow .2s;
    }

    .password-group:focus-within{
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        background: #fff;
    }

    .password-field{
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        font-size: 13px;
        letter-spacing: 1.5px;
        padding: 7px 10px;
        width: 100%;
        color: #1e293b;
    }

    /* BOTONES ICONO */
    .pwd-btn{
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        color: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.2);
        transition: filter .15s, transform .13s, box-shadow .15s;
    }

    .pwd-btn:active{
        transform: scale(.86) !important;
    }

    /* OJO — azul */
    .pwd-btn-eye{
        background: linear-gradient(135deg,#3b82f6,#1d4ed8);
    }

    .pwd-btn-eye:hover{
        filter: brightness(1.12);
        box-shadow: 0 4px 12px rgba(37,99,235,.45);
        transform: translateY(-1px);
    }

    .pwd-btn-eye.revealed{
        background: linear-gradient(135deg,#0ea5e9,#0369a1);
        box-shadow: 0 4px 12px rgba(14,165,233,.45);
    }

    /* COPIAR — violeta */
    .pwd-btn-copy{
        background: linear-gradient(135deg,#8b5cf6,#6d28d9);
    }

    .pwd-btn-copy:hover{
        filter: brightness(1.12);
        box-shadow: 0 4px 12px rgba(109,40,217,.45);
        transform: translateY(-1px);
    }

    .pwd-btn-copy.copied{
        background: linear-gradient(135deg,#10b981,#047857);
        box-shadow: 0 4px 12px rgba(16,185,129,.45);
    }

    .pwd-btn.copied i,
    .pwd-btn.revealed i{
        animation: pop .22s ease;
    }

    @keyframes pop{
        0%   { transform: scale(1); }
        50%  { transform: scale(1.4); }
        100% { transform: scale(1); }
    }

    /* BOTONES */
    .btn-action{
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* URL BUTTON */
    .btn-url{
        border-radius: 10px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
    }

    /* ALERT */
    .alert-success{
        border-radius: 12px;
        border: 1px solid #b7e4c7;
        background: #e9f9ee;
        color: #1b4332;
    }

</style>

<div class="container-fluid-fluid claves-wrapper">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h1 class="claves-title mb-0">
                🔐 Claves por Empresa
            </h1>

            <a href="{{ route('empresa-claves.create') }}"
               class="btn btn-primary px-4 py-2 rounded-3">

                <i class="bi bi-plus-circle"></i>
                Nueva Clave

            </a>

        </div>

        <div class="card claves-card mt-0">    

            <div class="card-body p-3">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h4 class="claves-subtitle mb-0">
                        Centro de Accesos
                    </h4>

                </div>

                {{-- MENSAJES --}}
                @if(session('success'))

                    <div class="alert alert-success">

                        {{ session('success') }}

                    </div>

                @endif

                {{-- TABLA --}}
                <div class="table-responsive">

                    <table class="table table-hover align-middle table-claves">

                        <thead>

                            <tr>

                                <th width="220">
                                    Servicio
                                </th>

                                <th width="180">
                                    Usuario
                                </th>

                                <th>
                                    Correo
                                </th>

                                <th width="280">
                                    Contraseña
                                </th>

                                <th width="120">
                                    URL
                                </th>

                                <th width="120">
                                    Acciones
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($claves as $clave)

                                <tr>

                                    {{-- SERVICIO --}}
                                    <td>

                                        <span class="servicio-badge">

                                            {{ $clave->servicio->nombre ?? '—' }}

                                        </span>

                                    </td>

                                    {{-- USUARIO --}}
                                    <td>

                                        <strong>

                                            {{ $clave->usuario ?? '—' }}

                                        </strong>

                                    </td>

                                    {{-- CORREO --}}
                                    <td>

                                        {{ $clave->correo_registrado ?? '—' }}

                                    </td>

                                    {{-- PASSWORD --}}
                                    <td>

                                        <div class="pw-wrapper">

                                            <div class="password-group">

                                                <input type="password"
                                                       readonly
                                                       class="form-control password-field"
                                                       value="{{ $clave->password ? Crypt::decryptString($clave->password) : '' }}">

                                            </div>

                                            <button type="button"
                                                    class="pwd-btn pwd-btn-eye toggle-password"
                                                    title="Mostrar contraseña">

                                                <i class="fa-solid fa-eye"></i>

                                            </button>

                                            <button type="button"
                                                    class="pwd-btn pwd-btn-copy copy-password"
                                                    title="Copiar contraseña">

                                                <i class="fa-solid fa-copy"></i>

                                            </button>

                                        </div>

                                    </td>

                                    {{-- URL --}}
                                    <td>

                                        @if(!empty($clave->servicio->url))

                                            <a href="{{ $clave->servicio->url }}"
                                               target="_blank"
                                               class="btn btn-outline-primary btn-sm btn-url">

                                                <i class="fa-solid fa-up-right-from-square"></i>

                                            </a>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>

                                    {{-- ACCIONES --}}
                                    <td>

                                        <div class="d-flex gap-2">

                                            {{-- EDITAR --}}
                                            <a href="{{ route('empresa-claves.edit', $clave) }}"
                                               class="btn btn-warning btn-sm btn-action">

                                                <i class="bi bi-pencil-square"></i>

                                            </a>

                                            {{-- ELIMINAR --}}
                                            <form action="{{ route('empresa-claves.destroy', $clave) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar clave?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm btn-action">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6"
                                        class="text-center text-muted py-5">

                                        <i class="bi bi-shield-lock fs-1"></i>

                                        <br><br>

                                        No hay claves registradas.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- PAGINACIÓN --}}
                <div class="mt-4">

                    {{ $claves->links() }}

                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function() {

    // MOSTRAR / OCULTAR PASSWORD
    document.querySelectorAll('.toggle-password').forEach(btn => {

        btn.addEventListener('click', function() {

            const input  = this.closest('.pw-wrapper').querySelector('.password-field');
            const icon   = this.querySelector('i');
            const hidden = input.type === 'password';

            input.type = hidden ? 'text' : 'password';
            icon.classList.replace(hidden ? 'fa-eye' : 'fa-eye-slash',
                                   hidden ? 'fa-eye-slash' : 'fa-eye');
            this.classList.toggle('revealed', hidden);
            this.title = hidden ? 'Ocultar contraseña' : 'Mostrar contraseña';

        });

    });

    // COPIAR PASSWORD
    document.querySelectorAll('.copy-password').forEach(btn => {

        btn.addEventListener('click', async function() {

            const input = this.closest('.pw-wrapper').querySelector('.password-field');
            const icon  = this.querySelector('i');

            try {

                await navigator.clipboard.writeText(input.value);

                icon.classList.replace('fa-copy', 'fa-check');
                this.classList.add('copied');
                this.title = '¡Copiado!';

                setTimeout(() => {

                    icon.classList.replace('fa-check', 'fa-copy');
                    this.classList.remove('copied');
                    this.title = 'Copiar contraseña';

                }, 1600);

            } catch {

                alert('No se pudo copiar la contraseña.');

            }

        });

    });

});

</script>

@endpush

