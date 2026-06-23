@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-person-check me-2"></i>Afiliaciones</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('afiliaciones.index') }}">Afiliaciones</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('afiliaciones.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">
<div class="row justify-content-center">
<div class="col-xl-10">
<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Nueva afiliación</h5>
    </div>
    <div class="card-body pt-4">
        @if($afiliados->isEmpty())
        <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2 mb-0">
            <span><i class="bi bi-exclamation-triangle me-2"></i>No hay afiliados registrados.</span>
            <a href="{{ route('afiliados.create') }}" class="btn btn-primary btn-sm">Crear Afiliado</a>
        </div>
        @else
        <form id="formAfiliacion" action="{{ route('afiliaciones.store') }}" method="POST">
            @csrf
            @include('modules.afiliaciones.form')
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Guardar
                </button>
                <a href="{{ route('afiliaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
        @endif
    </div>
</div>
</div>
</div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const salarios = @json($parametros);

    function bloquearFormulario() {
        $('.campo-form').prop('disabled', true);
    }

    function desbloquearFormulario() {
        $('.campo-form').prop('disabled', false);
    }

    bloquearFormulario();

    // FUNCIÓN CENTRAL (VALIDA TODO)
    function seleccionarAfiliado(af) {

        let estado = Number(af.estado) === 1;

        let tiene_afiliacion =
            af.tiene_afiliacion_activa == true ||
            af.tiene_afiliacion_activa == 1 ||
            af.tiene_afiliacion_activa == '1';

        let nombre = af.primer_nombre + ' ' + af.primer_apellido;
        let doc = af.numero_documento;

        // INACTIVO
        if (!estado) {
            alert('Este afiliado está inactivo y no puede ser afiliado.');
            bloquearFormulario();
            $('#afiliado_id').val('');
            $('#info_afiliado').removeClass('d-none').html(`
                <strong>${nombre}</strong><br>
                Documento: ${doc}<br>
                <span class="text-danger">AFILIADO INACTIVO</span>
            `);
            return;
        }

        // YA TIENE AFILIACIÓN
        if (tiene_afiliacion) {
            alert('Este afiliado ya tiene una afiliación activa.');
            bloquearFormulario();
            $('#afiliado_id').val('');
            $('#info_afiliado').removeClass('d-none').html(`
                <strong>${nombre}</strong><br>
                Documento: ${doc}<br>
                <span class="text-danger">YA TIENE AFILIACIÓN ACTIVA</span>
            `);
            return;
        }

        // VÁLIDO
        $('#afiliado_id').val(af.id);
        $('#info_afiliado').removeClass('d-none').html(`
            <strong>${nombre}</strong><br>
            Documento: ${doc}<br>
            <span class="text-success">AFILIADO DISPONIBLE</span>
        `);
        desbloquearFormulario();
    }

    // BUSCAR AFILIADO
    $('#btnBuscarAfiliado').click(function () {

        let buscar = $('#buscar_afiliado').val();

        if (!buscar) {
            alert('Escribe algo para buscar');
            return;
        }

        bloquearFormulario();
        $('#afiliado_id').val('');
        $('#info_afiliado').addClass('d-none').html('');

        axios.get("{{ route('afiliados.buscar') }}", {
            params: { buscar: buscar }
        })
        .then(function (response) {

            let lista = $('#lista_afiliados');
            lista.empty();

            if (response.data.length === 0) {
                alert('No se encontró ningún afiliado');
                $('#info_afiliado').removeClass('d-none').html(`
                    <span class="text-success">AFILIADO DISPONIBLE</span>
                `);
                return;
            }

            // AUTOSELECCIONAR SI SOLO HAY UNO
            if (response.data.length === 1) {
                seleccionarAfiliado(response.data[0]);
                return;
            }

            // MOSTRAR LISTA
            response.data.forEach(function (af) {
                lista.append(`
                    <a href="#" class="list-group-item afiliado-item"
                       data-id="${af.id}"
                       data-primer_nombre="${af.primer_nombre}"
                       data-primer_apellido="${af.primer_apellido}"
                       data-doc="${af.numero_documento}"
                       data-estado="${af.estado}"
                       data-tiene_afiliacion="${af.tiene_afiliacion_activa ? '1' : '0'}">
                       <strong>${af.primer_nombre} ${af.primer_apellido}</strong><br>
                       Documento: ${af.numero_documento}
                    </a>
                `);
            });

        });

    });

    // CLICK EN RESULTADO
    $(document).on('click', '.afiliado-item', function (e) {
        e.preventDefault();

        seleccionarAfiliado({
            id: $(this).data('id'),
            primer_nombre: $(this).data('primer_nombre'),
            primer_apellido: $(this).data('primer_apellido'),
            numero_documento: $(this).data('doc'),
            estado: $(this).attr('data-estado'),
            tiene_afiliacion_activa: $(this).attr('data-tiene_afiliacion') === '1'
        });

        $('#lista_afiliados').empty();
    });

    // IBC DINÁMICO
    function calcularSmmlv() {

        let fecha = $('#fecha_afiliacion').val();

        if (!fecha) {
            $('#ibc').val('');
            return;
        }

        let anio = new Date(fecha).getFullYear();
        let salario = salarios[anio] ?? 0;

        $('#ibc').val(salario);
    }

    // Cambia el TIPO: alterna readonly y solo aquí se limpia/recalcula el valor
    function actualizarIBC() {

        let tipo = $('#tipo_ibc').val();

        if (tipo === 'SMMLV') {
            $('#ibc').prop('readonly', true);
            calcularSmmlv();
        } else {
            $('#ibc').val('').prop('readonly', false);
        }
    }

    $('#tipo_ibc').on('change', actualizarIBC);

    // Cambia la FECHA: solo recalcula si es SMMLV, nunca toca el valor en FIJO
    $('#fecha_afiliacion').on('change', function () {
        if ($('#tipo_ibc').val() === 'SMMLV') {
            calcularSmmlv();
        }
    });

    actualizarIBC();

    // VALIDACIÓN FINAL
    $('#formAfiliacion').submit(function (e) {
        if (!$('#afiliado_id').val()) {
            e.preventDefault();
            alert('Debes seleccionar un afiliado válido');
        }
    });

});
</script>
@endpush
