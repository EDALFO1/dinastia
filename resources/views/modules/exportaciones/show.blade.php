@extends('layouts.main')

@section('titulo','Detalle del Lote')

@section('contenido')


<div class="container-fluid">

<div class="pagetitle">
    <h1>Lote {{ $batch->codigo }}</h1>
</div>

<section class="section">

<div class="card">
<div class="card-body">

<a href="{{ route('export.index') }}" class="btn btn-secondary mb-3">
← Volver
</a>

{{-- 📊 RESUMEN --}}
<div class="row mb-3">

<div class="col-md-3">
<strong>Periodo:</strong> {{ $batch->periodo }}
</div>

<div class="col-md-3">
<strong>Recibos:</strong> {{ $batch->recibos_count }}
</div>

<div class="col-md-3">
<strong>Total:</strong> ${{ number_format($batch->total,0) }}
</div>

<div class="col-md-3">
<strong>Fecha:</strong> {{ $batch->created_at }}
</div>

</div>

{{-- 💰 TOTALES POR CONCEPTO --}}
<div class="row mb-4">

<div class="col-md-3">
<div class="card text-center bg-primary text-white">
<div class="card-body">
<h6>EPS</h6>
<h4>${{ number_format($totales['eps'] ?? 0,0) }}</h4>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card text-center bg-success text-white">
<div class="card-body">
<h6>Pensión</h6>
<h4>${{ number_format($totales['pension'] ?? 0,0) }}</h4>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card text-center bg-warning text-dark">
<div class="card-body">
<h6>ARL</h6>
<h4>${{ number_format($totales['arl'] ?? 0,0) }}</h4>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card text-center bg-info text-white">
<div class="card-body">
<h6>Caja</h6>
<h4>${{ number_format($totales['caja'] ?? 0,0) }}</h4>
</div>
</div>
</div>

{{-- NUEVA FILA --}}
<div class="col-md-12 mt-3">
<div class="card text-center bg-dark text-white">
<div class="card-body">
<h5>Total General</h5>
<h3>${{ number_format($totales['total_general'] ?? 0,0) }}</h3>
</div>
</div>
</div>

</div>

<hr>

{{-- 📋 TABLA --}}
<table class="table table-striped">
<thead>
<tr>
    <th>#</th>
    <th>Afiliado</th>
    <th>IBC</th>
    <th>Total</th>
    <th>Novedad</th>
</tr>
</thead>

<tbody>

@foreach($batch->recibos as $r)
<tr>

<td>{{ $r->numero }}</td>

<td>
{{ optional($r->afiliado)->primer_nombre }}
{{ optional($r->afiliado)->primer_apellido }}
</td>

<td>${{ number_format($r->ibc,0) }}</td>

<td><strong>${{ number_format($r->total,0) }}</strong></td>

<td>
@if($r->novedad)
<span class="badge bg-warning">{{ $r->novedad }}</span>
@else
<span class="badge bg-success">Normal</span>
@endif
</td>

</tr>
@endforeach

</tbody>
</table>

</div>
</div>

</section>

</div>


@endsection