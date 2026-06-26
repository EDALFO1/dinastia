@extends('layouts.main')

@section('titulo', 'Empleados - Dinastía ERP')

@section('contenido')

<main id="main" class="main">

    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Empleados</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item active">Empleados</li>
                    </ol>
                </nav>
            </div>
            <a href="#" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> Nuevo Empleado
            </a>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Listado de Empleados</h5>

                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Juan García López</strong><br><small class="text-muted">Gerente</small></td>
                                    <td>1234567890</td>
                                    <td>juan@example.com</td>
                                    <td>310-555-1234</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                                        <a href="#" class="btn btn-sm btn-warning">Editar</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>María Rodríguez Pérez</strong><br><small class="text-muted">Contador</small></td>
                                    <td>9876543210</td>
                                    <td>maria@example.com</td>
                                    <td>310-555-5678</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">Ver</a>
                                        <a href="#" class="btn btn-sm btn-warning">Editar</a>
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
