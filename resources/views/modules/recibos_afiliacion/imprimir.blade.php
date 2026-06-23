<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Recibo Afiliación {{ $recibo->numero }}</title>
<style>
* { box-sizing: border-box; }

@page {
    size: 5.5in 8.5in;
    margin: 10mm 12mm;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 11px;
    color: #1e293b;
    margin: 0;
    padding: 12px;
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
    margin-bottom: 12px;
}

.doc-header .empresa h2 {
    margin: 0 0 2px;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.3px;
}

.doc-header .empresa p {
    margin: 0;
    font-size: 9px;
    color: #64748b;
}

.doc-header .folio {
    text-align: right;
}

.doc-header .folio .badge-recibo {
    display: inline-block;
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 3px 10px;
    font-weight: 700;
    font-size: 10px;
}

.doc-header .folio .fecha {
    margin-top: 3px;
    font-size: 9px;
    color: #64748b;
}

.info-grid {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 10px;
    margin-bottom: 12px;
}

.info-grid .col { font-size: 10px; line-height: 1.4; }
.info-grid .label { color: #94a3b8; font-size: 8px; text-transform: uppercase; letter-spacing: .03em; font-weight: 600; }
.info-grid .value { color: #0f172a; font-weight: 600; margin-top: 1px; }

table.detalle {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
}

table.detalle thead th {
    background: #f1f5f9;
    color: #475569;
    text-align: left;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 6px 8px;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 700;
}

table.detalle thead th.num { text-align: right; }

table.detalle tbody td {
    padding: 6px 8px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 10.5px;
}

table.detalle tbody td.num { text-align: right; }

table.detalle tfoot td {
    padding: 8px;
    font-size: 12px;
    font-weight: 800;
    border-top: 2px solid #0f172a;
}

table.detalle tfoot td.num {
    text-align: right;
    color: #2563eb;
}

.estado-pago {
    border-radius: 6px;
    padding: 8px;
    margin: 10px 0;
    text-align: center;
    font-weight: 700;
    font-size: 11px;
}

.estado-pago.pendiente {
    background: #fef3c7;
    border: 2px solid #fbbf24;
    color: #92400e;
}

.estado-pago.pagado {
    background: #dcfce7;
    border: 2px solid #4ade80;
    color: #166534;
}

.nota {
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 9px;
    color: #475569;
    line-height: 1.6;
    background: #f8fafc;
}

.nota strong { color: #0f172a; }

.no-print {
    text-align: right;
    margin-bottom: 12px;
}

.no-print button {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.no-print button:hover {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
}

.firma {
    display: flex;
    justify-content: space-around;
    margin-top: 16px;
    padding-top: 12px;
}

.firma-item {
    text-align: center;
    font-size: 8px;
}

.firma-item .linea {
    border-top: 1px solid #0f172a;
    width: 140px;
    margin: 12px auto 2px;
}

@media print {
    body { padding: 0; font-size: 11px; }
    .no-print { display: none; }
}

</style>
</head>

<body>

@php
    \Carbon\Carbon::setLocale('es');
@endphp

<div class="doc">

<div class="no-print">
    <button onclick="window.print()">🖨 Imprimir</button>
</div>

<div class="doc-header">
    <div class="empresa">
        <h2>{{ strtoupper($empresa->nombre) }}</h2>
        <p>NIT: {{ $empresa->nit }}</p>
    </div>
    <div class="folio">
        <span class="badge-recibo">Recibo Afiliación N° {{ $recibo->numero }}</span>
        <div class="fecha">
            {{ \Carbon\Carbon::parse($recibo->fecha)->translatedFormat('d \\d\\e F \\d\\e Y') }}
        </div>
    </div>
</div>

<div class="info-grid">
    <div class="col">
        <div class="label">Afiliado</div>
        <div class="value">{{ $recibo->afiliado?->primer_nombre }} {{ $recibo->afiliado?->primer_apellido }}</div>
        <div class="label" style="margin-top: 8px;">Dirección</div>
        <div class="value">{{ $recibo->afiliado?->direccion ?? 'N/A' }}</div>
    </div>
    <div class="col" style="text-align: right;">
        <div class="label">Documento</div>
        <div class="value">{{ $recibo->afiliado?->numero_documento ?? 'N/A' }}</div>
        <div class="label" style="margin-top: 8px;">Teléfono</div>
        <div class="value">{{ $recibo->afiliado?->telefono ?? 'N/A' }}</div>
        <div class="label" style="margin-top: 8px;">Correo</div>
        <div class="value" style="font-size:10px;">{{ $recibo->afiliado?->correo ?? 'N/A' }}</div>
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
        <tr>
            <td>{{ $recibo->concepto }}</td>
            <td class="num">${{ number_format($recibo->valor, 0, ',', '.') }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL A PAGAR</td>
            <td class="num">${{ number_format($recibo->valor, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

@if($recibo->notas)
<div class="nota">
    <strong>Observaciones:</strong><br>
    {{ $recibo->notas }}
</div>
@endif

<div class="estado-pago {{ $recibo->estado_pago }}">
    @if($recibo->estado_pago === 'pagado')
        ✓ PAGADO
        @if($recibo->fecha_pago)
            · {{ \Carbon\Carbon::parse($recibo->fecha_pago)->translatedFormat('d \\d\\e F \\d\\e Y') }}
        @endif
    @else
        ⏳ PENDIENTE DE PAGO
    @endif
</div>

<div class="firma">
    <div class="firma-item">
        <div class="linea"></div>
        Afiliado
    </div>
    <div class="firma-item">
        <div class="linea"></div>
        Asesor / Gestor
    </div>
</div>

</div>

</body>
</html>
