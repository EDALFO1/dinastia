@extends('layouts.main')

@section('titulo',$titulo)

@section('contenido')


<div class="pagetitle">
<h1>Editar Detalle</h1>
</div>

<section class="section">

<div class="card">
<div class="card-body pt-4">

<form action="{{ route('remision_detalles.update',$remision_detalle) }}" method="POST">

@csrf
@method('PUT')

@include('modules.remision_detalles.form')

<button class="btn btn-primary">Actualizar</button>

<a href="{{ route('remision_detalles.index') }}" class="btn btn-secondary">
Cancelar
</a>

</form>

</div>
</div>

</section>

@endsection