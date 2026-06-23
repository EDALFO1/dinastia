@extends('layouts.main')

@section('titulo','Activos siguiente periodo')

@section('contenido')


<div class="container-fluid">

<div class="pagetitle">
    <h1>Usuarios Activos Siguiente Periodo</h1>
</div>

<section class="section">

<div class="card">
<div class="card-body">

<a href="{{ route('recibos.index') }}" class="btn btn-secondary mb-3">
← Volver
</a>

<table class="table table-striped">
<thead>
<tr>
    <th>ID</th>
    <th>Documento</th>
    <th>Nombre</th>
    <th>Estado</th>
</tr>
</thead>

<tbody>

@forelse($activos as $r)
<tr>

<td>{{ $r->afiliado?->id }}</td>

<td>{{ $r->afiliado?->numero_documento ?? 'N/A' }}</td>

<td>
    {{ $r->afiliado?->primer_nombre }}
    {{ $r->afiliado?->primer_apellido }}
    <br>
    <small class="text-muted">
        {{ $r->afiliado?->numero_documento ?? 'N/A' }}
    </small>
</td>

<td>
<span class="badge bg-success">Activo siguiente periodo</span>
</td>

</tr>
@empty
<tr>
<td colspan="4" class="text-center">
No hay usuarios activos
</td>
</tr>
@endforelse

</tbody>
</table>

</div>
</div>

</section>
</div>


@endsection