@extends('layouts.main')

@section('titulo', 'Crear Asiento - Dinastía ERP')

@section('contenido')

<main id="main" class="main">

    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Crear Asiento Contable</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/journal-entries-new">Asientos</a></li>
                        <li class="breadcrumb-item active">Crear</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Nuevo Asiento Contable</h5>

                        <form class="row g-3">
                            <div class="col-md-6">
                                <label for="fecha" class="form-label">Fecha del Asiento</label>
                                <input type="date" class="form-control" id="fecha" required>
                            </div>

                            <div class="col-md-6">
                                <label for="concepto" class="form-label">Concepto</label>
                                <input type="text" class="form-control" id="concepto" placeholder="Ej: Venta de servicios" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Líneas del Asiento</label>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Cuenta</th>
                                                <th>Tipo</th>
                                                <th>Débito</th>
                                                <th>Crédito</th>
                                                <th><i class="bi bi-trash"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody id="lineas-table">
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" placeholder="Cuenta">
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm">
                                                        <option value="">Seleccionar</option>
                                                        <option>Débito</option>
                                                        <option>Crédito</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" placeholder="0.00" step="0.01">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" placeholder="0.00" step="0.01">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary mt-2">
                                    <i class="bi bi-plus"></i> Agregar Línea
                                </button>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Total Débitos</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="total-debitos" value="0.00" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Total Créditos</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="total-creditos" value="0.00" readonly>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Recuerda:</strong> El total de débitos debe ser igual al total de créditos.
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Crear Asiento
                                </button>
                                <a href="/journal-entries-new" class="btn btn-secondary">
                                    <i class="bi bi-x"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Guía de Creación</h5>
                        <p class="text-muted small">
                            <strong>Paso 1:</strong> Ingresa la fecha y concepto del asiento.
                        </p>
                        <p class="text-muted small">
                            <strong>Paso 2:</strong> Agrega las líneas con las cuentas contables.
                        </p>
                        <p class="text-muted small">
                            <strong>Paso 3:</strong> Asegúrate de que débitos = créditos.
                        </p>
                        <hr>
                        <p class="text-muted small">
                            <i class="bi bi-shield-check"></i>
                            El asiento será validado automáticamente.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

@endsection
