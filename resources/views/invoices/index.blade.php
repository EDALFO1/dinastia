@extends('layouts.main')

@section('titulo', 'Facturas - Dinastía ERP')

@section('contenido')

<main id="main" class="main">

    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Facturas</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item active">Facturas</li>
                    </ol>
                </nav>
            </div>
            <a href="#" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> Nueva Factura
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Facturas</h5>

                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado Local</th>
                                    <th>Estado DIAN</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>FV-0001</strong></td>
                                    <td>Empresa ABC S.A.S</td>
                                    <td>2026-06-20</td>
                                    <td>$5,000,000</td>
                                    <td><span class="badge bg-primary">Borrador</span></td>
                                    <td><span class="badge bg-warning">Pendiente</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                                        <a href="#" class="btn btn-sm btn-success">Firmar</a>
                                        <a href="#" class="btn btn-sm btn-primary">DIAN</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>FV-0002</strong></td>
                                    <td>Empresa XYZ Ltda</td>
                                    <td>2026-06-18</td>
                                    <td>$3,500,000</td>
                                    <td><span class="badge bg-success">Enviada</span></td>
                                    <td><span class="badge bg-success">Aceptada</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                                        <a href="#" class="btn btn-sm btn-danger">PDF</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

@endsection
