@extends('layouts.main')

@section('titulo', 'Recibos - Dinastía ERP')

@section('contenido')

<main id="main" class="main">

    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Recibos de Nómina</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item active">Recibos</li>
                    </ol>
                </nav>
            </div>
            <a href="#" class="btn btn-success btn-sm">
                <i class="bi bi-plus"></i> Nuevo Recibo
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recibos de Nómina</h5>

                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Recibo</th>
                                    <th>Empleado</th>
                                    <th>Periodo</th>
                                    <th>Devengado</th>
                                    <th>Descuentos</th>
                                    <th>Neto</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>REC-0001</strong></td>
                                    <td>Juan García López</td>
                                    <td>Junio 2026</td>
                                    <td>$3,500,000</td>
                                    <td>$800,000</td>
                                    <td><strong>$2,700,000</strong></td>
                                    <td><span class="badge bg-info">Enviado</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                                        <a href="#" class="btn btn-sm btn-danger">PDF</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>REC-0002</strong></td>
                                    <td>María Rodríguez Pérez</td>
                                    <td>Junio 2026</td>
                                    <td>$4,000,000</td>
                                    <td>$900,000</td>
                                    <td><strong>$3,100,000</strong></td>
                                    <td><span class="badge bg-success">Pagado</span></td>
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
