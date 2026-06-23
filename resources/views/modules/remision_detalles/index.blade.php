@extends('layouts.main')

@section('titulo',$titulo)

@section('contenido')


<div class="pagetitle">
<h1>Detalle Remisiones</h1>
</div>

<section class="section">

<div class="card">
<div class="card-body">

<div class="mt-3 mb-3">
<a href="{{ route('remision_detalles.create') }}" class="btn btn-primary">
Crear Detalle
</a>
</div>

<table class="table table-striped">

<thead>
<tr>
<th>ID</th>
<th>Remisión</th>
<th>Concepto</th>
<th>Valor</th>
<th width="150">Acciones</th>
</tr>
</thead>

<tbody>

@foreach($detalles as $d)

<tr>

<td>{{ $d->id }}</td>

<td>{{ $d->remision->numero }}</td>

<td>{{ $d->concepto }}</td>

<td>{{ number_format($d->valor,2) }}</td>

<td>

<a href="{{ route('remision_detalles.edit',$d) }}"
class="btn btn-warning btn-sm">
Editar
</a>

<form action="{{ route('remision_detalles.destroy',$d) }}"
method="POST"
style="display:inline">

@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
Eliminar
</button>

</form>

</td>

</tr>

@endforeach

</tbody>

</table>

{{ $detalles->links() }}

</div>
</div>

</section>

@endsection