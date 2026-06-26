@extends('layouts.main')

@section('titulo', 'Asientos Contables - Dinastía ERP')

@section('contenido')

<main id="main" class="main">

    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Asientos Contables</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item active">Asientos</li>
                    </ol>
                </nav>
            </div>
            <a href="#" class="btn btn-warning btn-sm">
                <i class="bi bi-plus"></i> Nuevo Asiento
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Asientos Contables</h5>

                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Asiento</th>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Débito</th>
                                    <th>Crédito</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>202206-001</strong></td>
                                    <td>2026-06-20</td>
                                    <td>Venta de servicios</td>
                                    <td>$10,000,000</td>
                                    <td>$10,000,000</td>
                                    <td><span class="badge bg-success">Posteado</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                                        <a href="#" class="btn btn-sm btn-secondary">Detalles</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>202206-002</strong></td>
                                    <td>2026-06-19</td>
                                    <td>Pago de nómina</td>
                                    <td>$5,800,000</td>
                                    <td>$5,800,000</td>
                                    <td><span class="badge bg-primary">Borrador</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                                        <a href="#" class="btn btn-sm btn-success">Aprobar</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Débitos (Junio)</h6>
                        <h3>$15,800,000</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Créditos (Junio)</h6>
                        <h3>$15,800,000</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">Saldo</h6>
                        <h3 class="text-success">$0</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

@endsection
