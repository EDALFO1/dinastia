@extends('layouts.main')

@section('titulo', 'Dashboard - Dinastía ERP')

@section('contenido')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="dashboard section">

        <div class="row">
            <!-- Empleados -->
            <div class="col-lg-3 col-md-6">
                <div class="info-card">
                    <div class="card-body">
                        <h5 class="card-title">Empleados</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="ps-3">
                                <h6>0</h6>
                                <small class="text-muted"><a href="/afiliados">Ver detalles</a></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recibos -->
            <div class="col-lg-3 col-md-6">
                <div class="info-card">
                    <div class="card-body">
                        <h5 class="card-title">Recibos</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="ps-3">
                                <h6>0</h6>
                                <small class="text-muted"><a href="/recibos">Ver detalles</a></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facturas -->
            <div class="col-lg-3 col-md-6">
                <div class="info-card">
                    <div class="card-body">
                        <h5 class="card-title">Facturas</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-credit-card"></i>
                            </div>
                            <div class="ps-3">
                                <h6>0</h6>
                                <small class="text-muted"><a href="/invoices">Ver detalles</a></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asientos -->
            <div class="col-lg-3 col-md-6">
                <div class="info-card">
                    <div class="card-body">
                        <h5 class="card-title">Asientos</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-table"></i>
                            </div>
                            <div class="ps-3">
                                <h6>0</h6>
                                <small class="text-muted"><a href="/journal-entries">Ver detalles</a></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Acciones Rápidas</h5>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="/afiliados/create" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-plus"></i> Nuevo Empleado
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/recibos/create" class="btn btn-sm btn-success w-100">
                                    <i class="bi bi-plus"></i> Nuevo Recibo
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/invoices/create" class="btn btn-sm btn-info w-100">
                                    <i class="bi bi-plus"></i> Nueva Factura
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/journal-entries/create" class="btn btn-sm btn-warning w-100">
                                    <i class="bi bi-plus"></i> Nuevo Asiento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

</main>

@endsection
