<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $invoice->numero }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
        }

        .company-info h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-info p {
            margin: 3px 0;
            font-size: 11px;
        }

        .invoice-title {
            flex: 1;
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 32px;
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .invoice-details {
            font-size: 11px;
            line-height: 1.6;
        }

        .invoice-details div {
            margin: 5px 0;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .parties {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            padding: 20px;
            background-color: #ecf0f1;
            border-radius: 5px;
        }

        .party {
            flex: 1;
            padding: 0 20px;
            border-right: 1px solid #bdc3c7;
        }

        .party:last-child {
            border-right: none;
        }

        .party h3 {
            font-size: 11px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .party p {
            font-size: 11px;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        table th {
            background-color: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #2c3e50;
        }

        table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            margin: 30px 0;
            width: 100%;
        }

        .totals table {
            margin: 0;
            width: auto;
            margin-left: auto;
        }

        .totals table th,
        .totals table td {
            padding: 8px 15px;
            border: 1px solid #ddd;
            text-align: right;
        }

        .totals table th {
            background-color: #2c3e50;
            color: white;
        }

        .total-amount {
            background-color: #e74c3c;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
        }

        .signature-box {
            width: 150px;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 10px;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .qr-section p {
            font-size: 10px;
            margin: 5px 0;
        }

        .stamp {
            position: absolute;
            opacity: 0.1;
            font-size: 60px;
            transform: rotate(-45deg);
            color: red;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            margin: 10px 0;
        }

        .status-borrador {
            background-color: #95a5a6;
            color: white;
        }

        .status-enviada {
            background-color: #3498db;
            color: white;
        }

        .status-aceptada {
            background-color: #27ae60;
            color: white;
        }

        .status-rechazada {
            background-color: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ $empresa->nombre ?? 'Empresa' }}</h1>
                <p><strong>NIT:</strong> {{ $empresa->nit ?? 'N/A' }}</p>
                <p><strong>Dirección:</strong> {{ $empresa->direccion ?? 'N/A' }}</p>
                <p><strong>Teléfono:</strong> {{ $empresa->telefono ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $empresa->email ?? 'N/A' }}</p>
            </div>
            <div class="invoice-title">
                <h2>FACTURA</h2>
                <div class="invoice-details">
                    <div>
                        <span class="label">No.:</span>
                        <span>{{ $invoice->numero }}</span>
                    </div>
                    <div>
                        <span class="label">Fecha:</span>
                        <span>{{ $invoice->fecha_emision->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="label">Vencimiento:</span>
                        <span>{{ $invoice->fecha_vencimiento->format('d/m/Y') }}</span>
                    </div>
                    @if($invoice->uuid_dian)
                    <div>
                        <span class="label">DIAN UUID:</span>
                        <span style="font-size: 10px;">{{ substr($invoice->uuid_dian, 0, 20) }}...</span>
                    </div>
                    @endif
                    <div style="margin-top: 10px;">
                        <span class="status-badge status-{{ $invoice->estado }}">
                            {{ strtoupper($invoice->estado) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party">
                <h3>Facturado A:</h3>
                <p><strong>{{ $invoice->cliente_nombre }}</strong></p>
                <p>NIT/Cédula: {{ $invoice->cliente_nit }}</p>
            </div>
            <div class="party">
                <h3>Remitir A:</h3>
                <p><strong>{{ $invoice->cliente_nombre }}</strong></p>
                <p>{{ $empresa->direccion ?? 'Misma dirección' }}</p>
            </div>
            <div class="party">
                <h3>Información de Entrega:</h3>
                <p><strong>{{ $invoice->cliente_nombre }}</strong></p>
                <p>{{ $empresa->direccion ?? 'Inmediata' }}</p>
            </div>
        </div>

        <!-- Line Items -->
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Unidad</th>
                    <th class="text-right">Valor Unitario</th>
                    <th class="text-right">Descuento %</th>
                    <th class="text-right">Total Línea</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lineItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->unit->value }}</td>
                    <td class="text-right">$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $item->discount_percent }}%</td>
                    <td class="text-right">$ {{ number_format($item->valor_linea, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Taxes and Totals -->
        <div class="totals">
            <table>
                <tr>
                    <th>Subtotal</th>
                    <td class="text-right">$ {{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                </tr>
                @if($invoice->descuento > 0)
                <tr>
                    <th>Descuento</th>
                    <td class="text-right">- $ {{ number_format($invoice->descuento, 2, ',', '.') }}</td>
                </tr>
                @endif
                @foreach($taxes as $tax)
                <tr>
                    <th>{{ $tax->tipo_impuesto->value }} ({{ $tax->porcentaje }}%)</th>
                    <td class="text-right">$ {{ number_format($tax->valor, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-amount">
                    <th>TOTAL</th>
                    <td class="text-right">$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- QR and DIAN Info -->
        @if($invoice->uuid_dian)
        <div class="qr-section">
            <p><strong>Documento Electrónico Certificado por la DIAN</strong></p>
            <p>UUID: {{ $invoice->uuid_dian }}</p>
            <p>Verificable en: <a href="https://www.dian.gov.co">www.dian.gov.co</a></p>
        </div>
        @endif

        <!-- Notes -->
        @if($invoice->observaciones)
        <div style="margin: 20px 0; padding: 15px; background-color: #f0f0f0; border-left: 4px solid #3498db;">
            <strong>Notas:</strong>
            <p>{{ $invoice->observaciones }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Este es un documento electrónico generado automáticamente por el sistema.</p>
        </div>
    </div>
</body>
</html>
