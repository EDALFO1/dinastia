<div class="row">

<div class="col-md-4 mb-3">

<label>Remisión</label>

<select name="remision_id" class="form-control">

@foreach($remisiones as $r)

<option value="{{ $r->id }}"
{{ old('remision_id',$remision_detalle->remision_id ?? '') == $r->id ? 'selected':'' }}>
{{ $r->numero }}
</option>

@endforeach

</select>

</div>


<div class="col-md-4 mb-3">

<label>Concepto</label>

<input
type="text"
name="concepto"
class="form-control"
value="{{ old('concepto',$remision_detalle->concepto ?? '') }}">

</div>


<div class="col-md-4 mb-3">

<label>Valor</label>

<input
type="number"
step="0.01"
name="valor"
class="form-control"
value="{{ old('valor',$remision_detalle->valor ?? '') }}">

</div>

</div>