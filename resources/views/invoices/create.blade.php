@extends('layouts.main')

@section('titulo', 'Crear Factura - Dinastía ERP')

@section('contenido')

<main id="main" class="main">

    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Crear Factura</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/invoices-new">Facturas</a></li>
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
                        <h5 class="card-title">Nueva Factura</h5>

                        <form class="row g-3">
                            <div class="col-md-6">
                                <label for="cliente" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="cliente" placeholder="Nombre del cliente" required>
                            </div>

                            <div class="col-md-6">
                                <label for="nit" class="form-label">NIT Cliente</label>
                                <input type="text" class="form-control" id="nit" placeholder="Ej: 123456789" required>
                            </div>

                            <div class="col-md-6">
                                <label for="fecha" class="form-label">Fecha de Factura</label>
                                <input type="date" class="form-control" id="fecha" required>
                            </div>

                            <div class="col-md-6">
                                <label for="numero" class="form-label">Número de Factura</label>
                                <input type="text" class="form-control" id="numero" placeholder="Ej: FV-0001" required>
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" rows="3" placeholder="Descripción de los servicios/productos"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="subtotal" class="form-label">Subtotal</label>
                                <input type="number" class="form-control" id="subtotal" placeholder="0.00" step="0.01">
                            </div>

                            <div class="col-md-6">
                                <label for="iva" class="form-label">IVA (%)</label>
                                <input type="number" class="form-control" id="iva" placeholder="19" value="19">
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Crear Factura
                                </button>
                                <a href="/invoices-new" class="btn btn-secondary">
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
                        <h5 class="card-title">Información</h5>
                        <p class="text-muted small">
                            <i class="bi bi-info-circle"></i>
                            Completa los datos de la factura. Los campos marcados con <strong>*</strong> son obligatorios.
                        </p>
                        <hr>
                        <p class="text-muted small">
                            <i class="bi bi-shield-check"></i>
                            La factura será validada contra DIAN antes de enviar.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

@endsection
