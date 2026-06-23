<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<title>Remisión {{ $remision->numero }}</title>

<style>

* { box-sizing: border-box; }

@page {
    size: 5.5in 8.5in;
    margin: 10mm 12mm;
}

body{
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 12px;
    color: #1e293b;
    margin: 0;
    padding: 16px;
}

.doc {
    max-width: 100%;
    margin: 0 auto;
}

.doc-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #3b82f6;
    padding-bottom: 8px;
    margin-bottom: 10px;
}

.doc-header .empresa h2 {
    margin: 0 0 2px;
    font-size: 14px;
    letter-spacing: -0.3px;
    color: #0f172a;
}

.doc-header .empresa p {
    margin: 0;
    font-size: 11px;
    color: #64748b;
}

.doc-header .folio {
    text-align: right;
}

.doc-header .folio .badge-remision {
    display: inline-block;
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 99px;
    padding: 3px 10px;
    font-weight: 700;
    font-size: 11px;
}

.doc-header .folio .fecha {
    margin-top: 4px;
    font-size: 10.5px;
    color: #64748b;
}

.info-grid {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 12px;
}

.info-grid .col { font-size: 11px; line-height: 1.5; }
.info-grid .label { color: #94a3b8; font-size: 9.5px; text-transform: uppercase; letter-spacing: .04em; }
.info-grid .value { color: #0f172a; font-weight: 600; }

table.detalle {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
}

table.detalle thead th {
    background: #f1f5f9;
    color: #475569;
    text-align: left;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 6px 8px;
    border-bottom: 2px solid #e2e8f0;
}

table.detalle thead th.num { text-align: right; }

table.detalle tbody td {
    padding: 6px 8px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 11.5px;
}

table.detalle tbody td.num { text-align: right; }

table.detalle tfoot td {
    padding: 8px;
    font-size: 13px;
    font-weight: 800;
    border-top: 2px solid #0f172a;
}

table.detalle tfoot td.num {
    text-align: right;
    color: #2563eb;
}

.nota {
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 10.5px;
    color: #475569;
    line-height: 1.6;
}

.nota strong { color: #0f172a; }

.no-print {
    text-align: right;
    margin-bottom: 20px;
}

.no-print button {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: #fff;
    border: none;
    padding: 10px 22px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

@media print{
    body { padding: 0; font-size: 11px; }
    .no-print { display: none; }
}

</style>

</head>

<body>

@php
    \Carbon\Carbon::setLocale('es');
    $periodoStr = ucfirst(\Carbon\Carbon::parse($remision->fecha)->subMonth()->translatedFormat('F Y'));
@endphp

<div class="doc">

<div class="no-print">
    <button onclick="window.print()">🖨 Imprimir</button>
</div>

<div class="doc-header">
    <div class="empresa">
        <h2>{{ strtoupper($remision->empresa->nombre) }}</h2>
        <p>NIT: {{ $remision->empresa->nit }}</p>
    </div>
    <div class="folio">
        <span class="badge-remision">Remisión N° {{ $remision->numero }}</span>
        <div class="fecha">
            {{ \Carbon\Carbon::parse($remision->fecha)->translatedFormat('d \\d\\e F \\d\\e Y') }}
        </div>
    </div>
</div>

<div class="info-grid">
    <div class="col">
        <div class="label">Afiliado</div>
        <div class="value">{{ $remision->afiliado?->primer_nombre }} {{ $remision->afiliado?->primer_apellido }}</div>
        <div class="label" style="margin-top:6px">Dirección</div>
        <div class="value">{{ $remision->afiliado?->direccion ?? 'N/A' }}</div>
    </div>
    <div class="col" style="text-align:right">
        <div class="label">Documento</div>
        <div class="value">{{ $remision->afiliado?->numero_documento ?? 'N/A' }}</div>
        <div class="label" style="margin-top:6px">Teléfono</div>
        <div class="value">{{ $remision->afiliado?->telefono ?? 'N/A' }}</div>
        <div class="label" style="margin-top:6px">Período de cotización</div>
        <div class="value" style="color:#2563eb">{{ $periodoStr }}</div>
    </div>
</div>

<table class="detalle">

<thead>
<tr>
    <th>Concepto</th>
    <th class="num">Valor</th>
</tr>
</thead>

<tbody>

@foreach($remision->detalles as $d)
<tr>
    <td>{{ $d->concepto }}</td>
    <td class="num">${{ number_format($d->valor) }}</td>
</tr>
@endforeach

</tbody>

<tfoot>
<tr>
    <td>TOTAL</td>
    <td class="num">${{ number_format($remision->total) }}</td>
</tr>
</tfoot>

</table>

<div class="nota">
    <strong>CRA 9 # 9 - 49</strong> · Tel: 8818282<br>
    No. cuenta: <strong>017070235944</strong> · Banco DAVIVIENDA, cta. ahorros<br>
    Enviar comprobante de consignación por WhatsApp al <strong>3152041979</strong> o <strong>3183375879</strong>
</div>

</div>

</body>
</html>
