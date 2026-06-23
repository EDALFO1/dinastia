@extends('layouts.main')

@section('titulo','Lotes PILA')

@section('contenido')



<div class="container-fluid">

<div class="pagetitle">
    <h1>Lotes Exportados</h1>
</div>

<section class="section">

<div class="card">
<div class="card-body">

<table class="table table-striped">
<thead>
<tr>
    <th>ID</th>
    <th>Código</th>
    <th>Periodo</th>
    <th>Recibos</th>
    <th>Total</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

@foreach($batches as $b)
<tr>

<td>{{ $b->id }}</td>

<td>{{ $b->codigo }}</td>

<td>{{ $b->periodo }}</td>

<td>{{ $b->recibos_count }}</td>

<td>${{ number_format($b->total,0) }}</td>

<td>

<a href="{{ route('export.descargar',$b->id) }}"
class="btn btn-success btn-sm">
Descargar
</a>

<a href="{{ route('export.show',$b->id) }}"
class="btn btn-primary btn-sm">
Ver
</a>

<form action="{{ route('export.reversar',$b->id) }}"
method="POST" style="display:inline">

@csrf

<button class="btn btn-danger btn-sm"
onclick="return confirm('¿Reversar este lote?')">
Reversar
</button>

</form>

</td>

</tr>
@endforeach

</tbody>
</table>

{{ $batches->links() }}

</div>
</div>

</section>
</div>


@endsection